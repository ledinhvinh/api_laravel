<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Thêm Auth để lấy thông tin người dùng nếu cần
use Illuminate\Http\JsonResponse; // Sử dụng JsonResponse
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception; // Sử dụng Exception gốc

class PayPalController extends Controller
{
    protected $payPalClient;

    /**
     * Khởi tạo PayPalClient và lấy Access Token.
     */
    public function __construct()
    {
        $this->payPalClient = new PayPalClient;

        // Load cấu hình từ .env vào config runtime
        // Đảm bảo các biến env đã được định nghĩa trong .env
        config([
            'paypal.mode'    => env('PAYPAL_MODE', 'sandbox'),
            'paypal.sandbox' => [
                'client_id'         => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
                'client_secret'     => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
                'app_id'            => 'APP-80W284485P519543T', // Thường không cần thiết cho REST API chuẩn
            ],
            'paypal.live' => [
                'client_id'         => env('PAYPAL_LIVE_CLIENT_ID', ''),
                'client_secret'     => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
                'app_id'            => env('PAYPAL_LIVE_APP_ID', ''), // Lấy từ .env nếu cần
            ],
            'paypal.payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // 'Sale', 'Authorization', 'Order'
            'paypal.currency'       => env('PAYPAL_CURRENCY', 'VND'),
            'paypal.notify_url'     => env('PAYPAL_NOTIFY_URL', ''), // URL cho IPN nếu dùng
            'paypal.locale'         => env('PAYPAL_LOCALE', 'en_US'), // ví dụ: en_US, vi_VN
            'paypal.validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true), // Nên là true ở production
            'paypal.settings.mode'  => env('PAYPAL_MODE', 'sandbox') // Đảm bảo setting mode cũng được đặt
        ]);

        try {
            // Thiết lập thông tin xác thực cho client
            $this->payPalClient->setApiCredentials(config('paypal'));
            // Lấy Access Token
            $this->payPalClient->getAccessToken();
        } catch (Exception $e) {
            Log::error('PAYPAL_ERROR: Không thể khởi tạo PayPal Client hoặc lấy Access Token.', [
                'message' => $e->getMessage()
            ]);
            // Có thể throw exception ở đây để dừng tiến trình nếu cần
            // throw new Exception("Không thể kết nối đến PayPal.");
        }
    }

    /**
     * API Endpoint: Tạo đơn hàng PayPal và trả về approval_url.
     * Route: POST /api/paypal/create-payment
     * Middleware: auth:sanctum
     *
     * @param Request $request Chứa thông tin đơn hàng (ví dụ: amount, description)
     * @return JsonResponse
     */
    public function createPayment(Request $request): JsonResponse
    {
        // --- Xác thực dữ liệu đầu vào ---
        $validated = $request->validate([
            // Ví dụ: Lấy amount từ request, bạn có thể lấy từ thông tin đơn hàng thực tế
            'order_id' => 'required|integer|exists:orders,id', // Ví dụ: yêu cầu ID đơn hàng từ client
            // 'amount' => 'required|numeric|min:0.01',
            // 'description' => 'nullable|string|max:127',
        ]);

        // --- Lấy thông tin đơn hàng từ DB (ví dụ) ---
        // Giả sử bạn có model Order và $validated['order_id'] là ID đơn hàng cần thanh toán
        try {
             $orderModel = \App\Models\Order::findOrFail($validated['order_id']);
             // Kiểm tra xem đơn hàng có thuộc về người dùng đang đăng nhập không
             if ($orderModel->user_id !== Auth::id()) {
                 return response()->json(['success' => false, 'message' => 'Không có quyền truy cập đơn hàng này.'], 403);
             }
             // Kiểm tra trạng thái đơn hàng, ví dụ: chỉ thanh toán đơn hàng 'pending'
             if ($orderModel->status !== 'pending') {
                  return response()->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ để thanh toán.'], 400);
             }
             $amountValue = $orderModel->total_amount; // Lấy tổng tiền từ đơn hàng
             $description = "Thanh toán cho đơn hàng #{$orderModel->id}";
        } catch (Exception $e) {
             Log::error('PAYPAL_ERROR: Không tìm thấy đơn hàng để thanh toán.', ['order_id' => $validated['order_id'], 'error' => $e->getMessage()]);
             return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }
        // --- Kết thúc lấy thông tin đơn hàng ---

        try {
            $paypalOrder = $this->payPalClient->createOrder([
                "intent" => "CAPTURE", // Thu tiền ngay
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => config('paypal.currency'),
                            "value" => number_format($amountValue, 2, '.', '') // Định dạng số tiền theo chuẩn PayPal
                        ],
                        'description' => $description,
                        'custom_id' => $orderModel->id // Lưu ID đơn hàng của bạn vào custom_id để tham chiếu khi capture
                    ]
                ],
                'application_context' => [
                    // Quan trọng: Các URL này thường trỏ về frontend trong luồng API
                    'return_url' => env('FRONTEND_URL') . '/payment/success', // URL frontend xử lý thành công
                    'cancel_url' => env('FRONTEND_URL') . '/payment/cancel',  // URL frontend xử lý hủy
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ]
            ]);

            // Kiểm tra kết quả và lấy link approve
            if (isset($paypalOrder['id']) && $paypalOrder['id'] != null) {
                $approvalUrl = null;
                foreach ($paypalOrder['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approvalUrl = $link['href'];
                        break;
                    }
                }

                if ($approvalUrl) {
                    // Lưu paypal_order_id vào đơn hàng của bạn để tham chiếu sau này
                    $orderModel->paypal_order_id = $paypalOrder['id'];
                    $orderModel->save();

                    Log::info('PAYPAL_INFO: Tạo đơn hàng PayPal thành công.', ['paypal_order_id' => $paypalOrder['id'], 'approval_url' => $approvalUrl]);
                    return response()->json([
                        'success' => true,
                        'approval_url' => $approvalUrl,
                        'paypal_order_id' => $paypalOrder['id'] // Trả về ID để client có thể lưu lại nếu cần
                    ], 200);
                }
            }

            // Nếu không thành công hoặc không có link approve
            Log::error('PAYPAL_ERROR: Tạo đơn hàng PayPal không thành công hoặc thiếu link approve.', ['response' => $paypalOrder ?? 'N/A']);
            return response()->json(['success' => false, 'message' => 'Không thể tạo đơn hàng PayPal.'], 500);

        } catch (Exception $e) {
            Log::error('PAYPAL_ERROR: Lỗi khi tạo đơn hàng PayPal.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Log chi tiết lỗi để debug
            ]);
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo đơn hàng PayPal.'], 500);
        }
    }

    /**
     * API Endpoint: Xử lý callback thành công từ PayPal (ít dùng trong API flow chuẩn).
     * Route: GET /api/paypal/success
     *
     * @param Request $request Chứa token (Order ID) và PayerID (nếu có)
     * @return JsonResponse
     */
    public function paymentSuccess(Request $request): JsonResponse
    {
        $token = $request->query('token');
        $payerId = $request->query('PayerID'); // Có thể có hoặc không

        Log::info('PAYPAL_INFO: Nhận callback thành công (GET /api/paypal/success).', ['token' => $token, 'payerId' => $payerId]);

        // Trong luồng API chuẩn, client sẽ xử lý redirect này và gọi capturePaymentApi.
        // Endpoint này chủ yếu để ghi log hoặc xử lý đơn giản nếu cần.
        if ($token) {
            // Bạn có thể trả về thông báo yêu cầu client gọi API capture
             return response()->json([
                'success' => true,
                'message' => 'Yêu cầu thành công. Vui lòng gọi API capture để hoàn tất thanh toán.',
                'token' => $token // Trả lại token để client sử dụng
          
          
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin token.'], 400);
        }
    }

    /**
     * API Endpoint: Xử lý callback hủy từ PayPal (ít dùng trong API flow chuẩn).
     * Route: GET /api/paypal/cancel
     *
     * @param Request $request Chứa token (Order ID) nếu có
     * @return JsonResponse
     */
    public function paymentCancel(Request $request): JsonResponse
    {
        $token = $request->query('token');
        Log::info('PAYPAL_INFO: Nhận callback hủy (GET /api/paypal/cancel).', ['token' => $token]);

        // Client thường xử lý việc hủy dựa trên redirect về cancel_url của frontend.
        return response()->json([
            'success' => false, // Coi như không thành công về mặt thanh toán
            'message' => 'Thanh toán đã bị hủy bởi người dùng.'
        ], 200); // Trả về 200 OK vì request hợp lệ, nhưng nội dung chỉ ra việc hủy
    }

    /**
     * API Endpoint: Client gọi để xác nhận và capture thanh toán.
     * Route: POST /api/paypal/capture-payment
     * Middleware: auth:sanctum
     *
     * @param Request $request Chứa 'token' (PayPal Order ID)
     * @return JsonResponse
     */
    public function capturePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string', // token ở đây là PayPal Order ID
        ]);

        $paypalOrderId = $validated['token'];
        $user = Auth::user(); // Lấy người dùng đang đăng nhập

        Log::info('PAYPAL_INFO: Nhận yêu cầu capture thanh toán.', ['user_id' => $user->id, 'paypal_order_id' => $paypalOrderId]);

        try {
            // --- Tìm đơn hàng tương ứng trong DB của bạn ---
             $orderModel = \App\Models\Order::where('paypal_order_id', $paypalOrderId)
                                        // ->where('user_id', $user->id) // Đảm bảo đơn hàng thuộc về user này
                                         ->first();

             if (!$orderModel) {
                 Log::warning('PAYPAL_WARN: Không tìm thấy đơn hàng ứng với PayPal Order ID hoặc không thuộc về user.', ['paypal_order_id' => $paypalOrderId, 'user_id' => $user->id]);
                 return response()->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ.'], 404);
             }

             // Kiểm tra trạng thái đơn hàng trước khi capture (ví dụ: phải là 'pending' hoặc đã có paypal_order_id)
             if ($orderModel->status !== 'pending') { // Hoặc trạng thái khác bạn dùng để chờ thanh toán
                 Log::warning('PAYPAL_WARN: Đơn hàng không ở trạng thái chờ thanh toán.', ['order_id' => $orderModel->id, 'status' => $orderModel->status]);
                 // Nếu đã thanh toán rồi thì trả về thành công luôn? Hoặc báo lỗi? Tùy logic của bạn.
                 if ($orderModel->status === 'paid') {
                      return response()->json(['success' => true, 'message' => 'Đơn hàng đã được thanh toán trước đó.'], 200);
                 }
                 return response()->json(['success' => false, 'message' => 'Trạng thái đơn hàng không hợp lệ để capture.'], 400);
             }
            // --- Kết thúc kiểm tra đơn hàng ---


            // Gọi API capture của PayPal
            $response = $this->payPalClient->capturePaymentOrder($paypalOrderId);

            Log::info('PAYPAL_INFO: Phản hồi từ PayPal Capture API.', ['paypal_order_id' => $paypalOrderId, 'response_status' => $response['status'] ?? 'N/A']);

            // Kiểm tra trạng thái capture
            if (isset($response['status']) && strtoupper($response['status']) == 'COMPLETED') {
                // Thanh toán thành công!
                $transactionId = $response['id']; // PayPal Order ID
                $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null; // ID của giao dịch capture cụ thể

                // --- Cập nhật trạng thái đơn hàng trong Database ---
                $orderModel->status = 'paid'; // Hoặc 'processing', 'completed' tùy quy trình của bạn
                $orderModel->transaction_id = $captureId ?? $transactionId; // Lưu ID giao dịch PayPal
                $orderModel->payment_method = 'paypal';
                $orderModel->paid_at = now(); // Thời gian thanh toán
                $orderModel->save();

                // Có thể thêm các hành động khác: gửi email xác nhận, tạo hóa đơn,...
                // dispatch(new \App\Jobs\SendOrderConfirmationEmail($orderModel));

                Log::info('PAYPAL_SUCCESS: Capture thanh toán thành công.', [
                    'order_id' => $orderModel->id,
                    'paypal_order_id' => $transactionId,
                    'capture_id' => $captureId
                ]);
                // --- Kết thúc cập nhật DB ---

                return response()->json([
                    'success' => true,
                    'message' => 'Thanh toán thành công!',
                    'data' => [
                        'order_id' => $orderModel->id, // ID đơn hàng của bạn
                        'transaction_id' => $captureId ?? $transactionId, // ID giao dịch PayPal
                        'status' => $orderModel->status
                    ]
                ], 200);

            } else {
                // Capture không thành công hoặc trạng thái không phải COMPLETED
                Log::error('PAYPAL_ERROR: Capture thanh toán không thành công hoặc trạng thái không COMPLETED.', [
                    'paypal_order_id' => $paypalOrderId,
                    'response' => $response // Log toàn bộ response để debug
                ]);
                // Không nên đổi trạng thái đơn hàng ở đây
                return response()->json([
                    'success' => false,
                    'message' => 'Xác nhận thanh toán không thành công từ PayPal.',
                    'details' => $response // Có thể trả về chi tiết lỗi nếu cần
                ], 400); // Bad Request hoặc lỗi từ phía PayPal
            }

        } catch (Exception $e) {
            Log::error('PAYPAL_ERROR: Lỗi nghiêm trọng khi capture thanh toán.', [
                'paypal_order_id' => $paypalOrderId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống khi xác nhận thanh toán.'], 500);
        }
    }
}

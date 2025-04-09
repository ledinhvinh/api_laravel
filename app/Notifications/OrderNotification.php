<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderNotification extends Notification
{
    use Queueable;
    private $order;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        //
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        
            return ['mail', 'database']; // Gửi qua email và lưu vào database
        
    
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác nhận đơn hàng #' . $this->order->id)
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Đơn hàng của bạn đã được đặt thành công.')
            ->line('Mã đơn hàng: ' . $this->order->id)
            ->action('Xem đơn hàng', url('/orders/' . $this->order->id))
            ->line('Cảm ơn bạn đã mua sắm!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
            'order_id' => $this->order->id,
            'message'  => 'Đơn hàng #' . $this->order->id . ' đã được xác nhận.',
        ];
    }
}

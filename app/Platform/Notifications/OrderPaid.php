<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Order;
use App\Platform\Money;

/** Paiement confirmé : au client (e-mail + cloche) ; aux administrateurs de la plateforme (cloche). */
class OrderPaid extends Notice
{
    public function __construct(private Order $order, private bool $forAdmin = false) {}

    public function title(): array
    {
        return $this->forAdmin
            ? ['Commande payée : :number (:amount)', ['number' => $this->order->number, 'amount' => Money::format($this->order->total, $this->order->currency)]]
            : ['Paiement reçu : commande :number', ['number' => $this->order->number]];
    }

    public function body(): ?array
    {
        return $this->forAdmin
            ? ['Client : :name', ['name' => (string) $this->order->customer?->name]]
            : ['Merci ! Montant : :amount. Votre commande est livrée.', ['amount' => Money::format($this->order->total, $this->order->currency)]];
    }

    public function url(): ?string
    {
        return $this->forAdmin ? route('admin.platform.orders.show', $this->order) : route('platform.customer.orders.show', $this->order);
    }

    public function icon(): string
    {
        return 'payment';
    }

    public function mail(object $notifiable): bool
    {
        return ! $this->forAdmin;
    }
}

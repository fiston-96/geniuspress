<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Customer;

/** Nouveau client inscrit (cloche des administrateurs de la plateforme). */
class NewCustomer extends Notice
{
    public function __construct(private Customer $customer) {}

    public function title(): array
    {
        return ['Nouveau client : :name', ['name' => $this->customer->name]];
    }

    public function body(): ?array
    {
        return [':email', ['email' => $this->customer->email]];
    }

    public function url(): ?string
    {
        return route('admin.platform.customers.show', $this->customer);
    }

    public function icon(): string
    {
        return 'user';
    }
}

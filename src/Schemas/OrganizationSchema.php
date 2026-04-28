<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Schemas;

class OrganizationSchema extends BaseSchema
{
    public function __construct()
    {
        parent::__construct('Organization');
    }

    public function name(string $name): self
    {
        return $this->set('name', $name);
    }

    public function url(string $url): self
    {
        return $this->set('url', $url);
    }

    public function logo(string $logo): self
    {
        return $this->set('logo', $logo);
    }

    public function description(string $description): self
    {
        return $this->set('description', $description);
    }

    public function email(string $email): self
    {
        return $this->set('email', $email);
    }

    public function telephone(string $telephone): self
    {
        return $this->set('telephone', $telephone);
    }

    public function address(array $address): self
    {
        return $this->set('address', [
            '@type' => 'PostalAddress',
            ...$address,
        ]);
    }

    public function sameAs(array $urls): self
    {
        return $this->set('sameAs', $urls);
    }

    public function contactPoint(string $telephone, string $contactType = 'customer service', ?string $areaServed = null): self
    {
        $contact = [
            '@type' => 'ContactPoint',
            'telephone' => $telephone,
            'contactType' => $contactType,
        ];

        if ($areaServed) {
            $contact['areaServed'] = $areaServed;
        }

        $current = $this->properties['contactPoint'] ?? [];
        $current[] = $contact;

        return $this->set('contactPoint', $current);
    }
}

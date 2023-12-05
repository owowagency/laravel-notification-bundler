<?php

use Owowagency\NotificationBundler\Tests\Support\BundledMailNotification;
use Owowagency\NotificationBundler\Tests\Support\NotifiableModel;
use Owowagency\NotificationBundler\Tests\Support\OtherBundledMailNotification;

describe('different mail notifications', function () {
    beforeEach(function () {
        $this->freezeTime();
        $this->travelTo('2023-06-07 12:00:00');

        $this->mailTransport = app()->make('mailer')->getSymfonyTransport();

        $this->notifiable = NotifiableModel::query()->create([
            'email' => 'test@owow.io',
        ]);
    });

    it('can be bundled', function () {
        $this->notifiable->notify(new OtherBundledMailNotification('Robin'));

        $this->notifiable->notify(new BundledMailNotification('Pieter'));

        $this->travelTo('2023-06-07 12:00:30');

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        /* @var \Symfony\Component\Mime\Email $email */
        $email = $this->mailTransport->messages()->first()->getOriginalMessage();

        expect($email->getTo())->toHaveCount(1);
        expect($email->getTo()[0]->getAddress())->toBe('test@owow.io');
        expect($email->getSubject())->toBe('Other Bundle');
        expect($email->getTextBody())->toContain('Other Robin was bundled.', 'Original Pieter was bundled.');
    });
});

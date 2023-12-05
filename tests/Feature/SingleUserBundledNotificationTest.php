<?php

use Illuminate\Support\Facades\Queue;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\Tests\Support\BundledMailNotification;
use Owowagency\NotificationBundler\Tests\Support\NotifiableModel;

describe('single user bundled mail notification', function () {
    beforeEach(function () {
        $this->freezeTime();
        $this->travelTo('2023-06-07 12:00:00');

        $this->mailTransport = app()->make('mailer')->getSymfonyTransport();

        $this->notifiable = NotifiableModel::query()->create([
            'email' => 'test@owow.io',
        ]);
    });

    it('can be sent', function () {
        $this->notifiable->notify(new BundledMailNotification('Robin'));

        expect(Queue::size())->toBe(1);
        expect(NotificationBundle::count())->toBe(1);

        $this->travelTo('2023-06-07 12:00:02');

        $this->notifiable->notify(new BundledMailNotification('Pieter'));

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(2);

        $this->travelTo('2023-06-07 12:00:05');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(1);
        expect(NotificationBundle::count())->toBe(2);
        expect($this->mailTransport->messages()->count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:07');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(1);

        /* @var \Symfony\Component\Mime\Email $email */
        $email = $this->mailTransport->messages()->first()->getOriginalMessage();

        expect($email->getTo())->toHaveCount(1);
        expect($email->getTo()[0]->getAddress())->toBe('test@owow.io');
        expect($email->getSubject())->toBe('Bundle');
        expect($email->getTextBody())->toContain('Robin was bundled.', 'Pieter was bundled.');
    });
});

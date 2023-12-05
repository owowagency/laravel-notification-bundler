<?php

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\Tests\Support\BundledMailNotification;
use Owowagency\NotificationBundler\Tests\Support\NotifiableModel;

describe('multi user bundled mail notification', function () {
    beforeEach(function () {
        $this->freezeTime();
        $this->travelTo('2023-06-07 12:00:00');

        $this->mailTransport = app()->make('mailer')->getSymfonyTransport();

        $this->notifiables = [
            NotifiableModel::query()->create([
                'email' => 'test@owow.io',
            ]),
            NotifiableModel::query()->create([
                'email' => 'user@owow.io',
            ]),
        ];
    });

    it('can be sent', function () {
        Notification::send($this->notifiables, new BundledMailNotification('Robin'));

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(2);

        $this->travelTo('2023-06-07 12:00:10');

        Notification::send($this->notifiables, new BundledMailNotification('Pieter'));

        expect(Queue::size())->toBe(4);
        expect(NotificationBundle::count())->toBe(4);

        $this->travelTo('2023-06-07 12:00:30');

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(4);
        expect($this->mailTransport->messages()->count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:40');

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(2);

        foreach (['test@owow.io', 'user@owow.io'] as $index => $address) {
            /* @var \Symfony\Component\Mime\Email $email */
            $email = $this->mailTransport->messages()[$index]->getOriginalMessage();

            expect($email->getTo())->toHaveCount(1);
            expect($email->getTo()[0]->getAddress())->toBe($address);
            expect($email->getSubject())->toBe('Bundle');
            expect($email->getTextBody())->toMatchSnapshot();
        }
    });
});

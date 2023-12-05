<?php

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\Tests\Support\MailNotification;
use Owowagency\NotificationBundler\Tests\Support\NotifiableModel;

describe('multi user normal mail notification', function () {
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
        Notification::send($this->notifiables, new MailNotification('Robin'));

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:02');

        Notification::send($this->notifiables, new MailNotification('Pieter'));

        expect(Queue::size())->toBe(4);
        expect(NotificationBundle::count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:05');

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(2);

        $this->travelTo('2023-06-07 12:00:07');

        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(4);

        $expects = [
            ['Robin', 'test@owow.io'],
            ['Robin', 'user@owow.io'],
            ['Pieter', 'test@owow.io'],
            ['Pieter', 'user@owow.io'],
        ];

        foreach ($expects as $index => $expect) {
            /* @var \Symfony\Component\Mime\Email $email */
            $email = $this->mailTransport->messages()[$index]->getOriginalMessage();

            [$name, $address] = $expect;

            expect($email->getTo())->toHaveCount(1);
            expect($email->getTo()[0]->getAddress())->toBe($address);
            expect($email->getSubject())->toBe('Normal');
            expect($email->getTextBody())->toContain("$name was not bundled.");
        }
    });
});

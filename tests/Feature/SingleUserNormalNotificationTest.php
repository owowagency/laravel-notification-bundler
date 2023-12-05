<?php

use Illuminate\Support\Facades\Queue;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\Tests\Support\MailNotification;
use Owowagency\NotificationBundler\Tests\Support\NotifiableModel;

describe('single user normal mail notification', function () {
    beforeEach(function () {
        $this->freezeTime();
        $this->travelTo('2023-06-07 12:00:00');

        $this->mailTransport = app()->make('mailer')->getSymfonyTransport();

        $this->notifiable = NotifiableModel::query()->create([
            'email' => 'test@owow.io',
        ]);
    });

    it('can be sent', function () {
        $this->notifiable->notify(new MailNotification('Robin'));

        expect(Queue::size())->toBe(1);
        expect(NotificationBundle::count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:02');

        $this->notifiable->notify(new MailNotification('Pieter'));

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:05');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(1);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(1);

        $this->travelTo('2023-06-07 12:00:07');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(2);

        foreach (['Robin', 'Pieter'] as $index => $name) {
            /* @var \Symfony\Component\Mime\Email $email */
            $email = $this->mailTransport->messages()[$index]->getOriginalMessage();

            expect($email->getTo())->toHaveCount(1);
            expect($email->getTo()[0]->getAddress())->toBe('test@owow.io');
            expect($email->getSubject())->toBe('Normal');
            expect($email->getTextBody())->toContain("$name was not bundled.");
        }
    });
});

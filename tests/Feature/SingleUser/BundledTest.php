<?php

use Illuminate\Support\Facades\Queue;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\Tests\Support\BundledMailNotification;
use Owowagency\NotificationBundler\Tests\Support\BundledMultiChannelNotification;
use Owowagency\NotificationBundler\Tests\Support\FailingBundledMailNotification;
use Owowagency\NotificationBundler\Tests\Support\NotifiableModel;
use Owowagency\NotificationBundler\Tests\Support\SingleDelayBundledMailNotification;

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

        $this->travelTo('2023-06-07 12:00:10');

        $this->notifiable->notify(new BundledMailNotification('Pieter'));

        expect(Queue::size())->toBe(2);
        expect(NotificationBundle::count())->toBe(2);

        $this->travelTo('2023-06-07 12:00:30');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(1);
        expect(NotificationBundle::count())->toBe(2);
        expect($this->mailTransport->messages()->count())->toBe(0);

        $this->travelTo('2023-06-07 12:00:40');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect(NotificationBundle::count())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(1);

        /* @var \Symfony\Component\Mime\Email $email */
        $email = $this->mailTransport->messages()->first()->getOriginalMessage();

        expect($email->getTo())->toHaveCount(1);
        expect($email->getTo()[0]->getAddress())->toBe('test@owow.io');
        expect($email->getSubject())->toBe('Bundle');
        expect($email->getTextBody())->toMatchSnapshot();
    });

    it('deletes records from database if notification fails', function () {
        $this->notifiable->notify(new FailingBundledMailNotification(1));

        $this->travelTo('2023-06-07 12:00:30');

        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect(NotificationBundle::count())->toBe(0);
    });

    it('sends other notifications if first fails', function () {
        $this->notifiable->notify(new FailingBundledMailNotification(1));

        $this->travelTo('2023-06-07 12:00:10');

        $this->notifiable->notify(new BundledMailNotification('Pieter'));

        $this->travelTo('2023-06-07 12:00:30');
        $this->artisan('queue:work --once');

        $this->travelTo('2023-06-07 12:00:40');
        $this->artisan('queue:work --once');

        /* @var \Symfony\Component\Mime\Email $email */
        $email = $this->mailTransport->messages()->first()->getOriginalMessage();

        expect($email->getTextBody())->toMatchSnapshot();
    });

    it('sends other notifications if last fails', function () {
        $this->notifiable->notify(new BundledMailNotification('Robin'));

        $this->travelTo('2023-06-07 12:00:10');

        $this->notifiable->notify(new FailingBundledMailNotification(1));

        $this->travelTo('2023-06-07 12:00:30');
        $this->artisan('queue:work --once');

        $this->travelTo('2023-06-07 12:00:40');
        $this->artisan('queue:work --once');

        /* @var \Symfony\Component\Mime\Email $email */
        $email = $this->mailTransport->messages()->first()->getOriginalMessage();

        expect($email->getTextBody())->toMatchSnapshot();
    });

    it('can be sent on multiple channels', function () {
        $this->notifiable->notify(new BundledMultiChannelNotification('Robin'));

        $this->travelTo('2023-06-07 12:00:10');

        $this->notifiable->notify(new BundledMultiChannelNotification('Pieter'));

        $this->travelTo('2023-06-07 12:00:30');
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        $this->travelTo('2023-06-07 12:00:40');
        $this->artisan('queue:work --once');
        $this->artisan('queue:work --once');

        expect($this->mailTransport->messages()->count())->toBe(1);

        /* @var \Symfony\Component\Mime\Email $email */
        $email = $this->mailTransport->messages()->first()->getOriginalMessage();

        expect($email->getTextBody())->toMatchSnapshot();

        expect($this->notifiable->notifications->count())->toBe(1);
        expect($this->notifiable->notifications->first()->data)->toEqual(['names' => ['Pieter', 'Robin']]);
    });

    it('can change the delay per notification', function () {
        $this->notifiable->notify(new SingleDelayBundledMailNotification(60));

        $this->travelTo('2023-06-07 12:00:40');
        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(1);

        $this->travelTo('2023-06-07 12:01:00');
        $this->artisan('queue:work --once');

        expect(Queue::size())->toBe(0);
        expect($this->mailTransport->messages()->count())->toBe(1);
    });
});

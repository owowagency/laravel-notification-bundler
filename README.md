![banner-dark](.github/assets/banner-dark.svg#gh-dark-mode-only)
![banner-light](.github/assets/banner-light.svg#gh-light-mode-only)

<p align="center">
    <a href="https://github.com/owowagency/laravel-notification-bundler/releases">
        <img src="https://img.shields.io/github/release/owowagency/laravel-notification-bundler.svg?logo=github" alt="Release shield">
    </a>
    <a href="https://github.com/owowagency/laravel-notification-bundler/actions/workflows/test.yml?query=branch%3Amain">
        <img src="https://img.shields.io/github/actions/workflow/status/owowagency/laravel-notification-bundler/test.yml?branch=main&label=tests&logo=github" alt="Workflow shield">
    </a>
    <a href="https://packagist.org/packages/owowagency/laravel-notification-bundler">
        <img src="https://img.shields.io/packagist/dt/owowagency/laravel-notification-bundler.svg?logo=packagist" alt="Downloads shield">
    </a>
</p>

<p align="center">
    A package for Laravel that bundles notifications sent within a specified delay for a single user.
</p>

# 📖 Table of contents

1. [Demo](#-demo)
2. [Installation](#-installation)
3. [Usage](#-usage)
    1. [Changing the delay](#changing-the-delay)
4. [Contributing](#-contributing)
5. [License](#-license)
6. [OWOW](#-owow)

## 🚀 Demo

TODO

## ⚙️ Installation

Installing this package can be done by using `Composer`:

```bash
composer require owowagency/laravel-notification-bundler
```

## 🛠️ Usage

Here is a simple example of how to use this package.

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class BundledMailNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
{
    use BundlesNotifications, Queueable;

    public function __construct(public string $name)
    {
        //
    }

    /**
     * The channels the notification should be sent on.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * This replaces the original `toMail` method and adds the $notifications 
     * collection as the last parameter.
     */
    public function toMailBundle(object $notifiable, Collection $notifications)
    {
        $message = (new MailMessage)
            ->subject('Bundle');

        foreach ($notifications as $notification) {
            $message->line("$notification->name was bundled.");
        }

        return $message;
    }

    /**
     * Returns the identifier for the bundle.
     * This is used to determine which notifications should be bundled together.
     * This also means that different notifications can be bundled together.
     */
    public function bundleIdentifier(object $notifiable): string
    {
        return "user_$notifiable->id";
    }
}
```

Because of limitations in Laravel, the database channel must implicitly use the `toArray`, or `toDatabase` method.
To get the notifications in those functions, you can use the `getBundle()` method.

```php
public function toDatabase(object $notifiable)
{
    $notifications = $this->getBundle();
    return ['names' => $notifications->pluck('name')->toArray()];
}
```

### Changing the delay

By default, the delay is set to 30 seconds. 
You can change this delay by publishing the config file and changing the `bundle_notifications_after_seconds` value.

```bash
php artisan vendor:publish --provider="Owowagency\NotificationBundler\NotificationBundlerServiceProvider" --tag="config"
```

To change it per notification, the `bundleDelay()` method can be used.

```php
public function bundleDelay(object $notifiable): int|\DateTimeInterface
{
    return 60;
}
```

To take even more control, you can use the `withDelay()` method to specify a delay per channel.

```php
public function withDelay(object $notifiable): array
{
    return [
        'mail' => 30,
        'sms' => 60,
    ];
}
```

## 🫶 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

<br>

<img id="owow" src=".github/assets/owow-light.svg#gh-light-mode-only" width="150">
<img id="owow" src=".github/assets/owow-dark.svg#gh-dark-mode-only" width="150">

This package has been brought to you with much love by the wizkids of [OWOW](https://owow.io/). 
Do you like this package? We’re still looking for new talent and Wizkids. 
So do you want to contribute to open source, while getting paid? [Apply now](https://owow.io/careers).

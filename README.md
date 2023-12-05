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
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * This replaces the original `toMail` method and adds the $notifications 
     * collection as the last parameter.
     */
    public function toMailBundle($notifiable, Collection $notifications)
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
    public function bundleIdentifier($notifiable): string
    {
        return "user_$notifiable->id";
    }
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

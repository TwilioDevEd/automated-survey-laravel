#  Automated Surveys Using Laravel

[![Build Status](https://travis-ci.org/TwilioDevEd/automated-survey-laravel.svg?branch=master)](https://travis-ci.org/TwilioDevEd/automated-survey-laravel)

This application demonstrates how to use Twilio and TwiML to perform
automated phone surveys.

[Read the full tutorial](https://www.twilio.com/docs/tutorials/walkthrough/automated-survey/php/laravel)!

## Running locally

1. Clone the repository and `cd` into it.

1. Install the application's dependencies with [Composer](https://getcomposer.org/).

   ```bash
   $ composer install
   ```

1. The application uses PostgreSQL as the persistence layer. You should install it
  if you don't have it. The easiest way is by using [Postgres.app](http://postgresapp.com/).

1. Create a database.

  ```bash
  $ createdb surveys
  ```

1. Copy the sample configuration file and edit it to match your configuration.

   ```bash
   $ cp .env.example .env
   ```

1. Generate an `APP_KEY`.

   ```bash
   $ php artisan key:generate
   ```

1. Run the migrations.

  ```bash
  $ php artisan migrate
  ```

1. Load a survey.

  ```bash
  $ php artisan heroku:initialize bear_survey.json
  ```

1. Expose the application to the wider Internet using [ngrok](https://ngrok.com/)

   ```bash
   $ ngrok http 8000
   ```
   Now you have a public URL that will forward requests to your localhost. It should
   look like this:

   ```
   http://<your-ngrok-subdomain>.ngrok.io
   ```

1. Configure Twilio to call your webhooks.

   You will also need to configure Twilio to send requests to your application
   when an SMS or a voice call is received.

   You will need to provision at least one Twilio number with SMS and voice capabilities.
   You can buy a number [right
   here](https://www.twilio.com/user/account/phone-numbers/search). Once you have
   a number you need to configure it to work with your application. Open
   [the number management page](https://www.twilio.com/user/account/phone-numbers/incoming)
   and open a number's configuration by clicking on it.

   ![Configure Voice](http://howtodocs.s3.amazonaws.com/twilio-number-config-all-med.gif)

   For this application you must set the voice webhook of your number.
   It will look something like this:

   ```
   http://<your-ngrok-subdomain>.ngrok.io/voice/connect
   ```

   The SMS webhook should look something like this:

   ```
   http://<your-ngrok-subdomain>.ngrok.io/sms/connect
   ```

   For this application you must set the `POST` method on the configuration for both webhooks.

1. Run the application using Artisan.

   ```bash
   $ php artisan serve
   ```

   It is `artisan serve` default behavior to use `http://localhost:8000` when
   the application is run. This means that the ip addresses where your app will be
   reachable on you local machine will vary depending on the operating system.

   The most common scenario is that your app will be reachable through address
   `http://127.0.0.1:8000`. This is important because ngrok creates the
   tunnel using that address only. So, if `http://127.0.0.1:8000` is not reachable
   in your local machine when you run the app, you must set it so that artisan uses this
   address. Here's how to set that up:

   ```bash
   $ php artisan serve --host=127.0.0.1
   ```

## How to Demo

1. Set up your application to run locally or in production.

1. Update your [Twilio Number](https://www.twilio.com/user/account/phone-numbers/incoming)'s
   voice and SMS webhooks with your ngrok url.

1. Give your number a call or send yourself an SMS with the "start" command.

1. Follow the instructions until you answer all the questions.

1. When you are notified that you have reached the end of the survey, visit your
   application's root to check the results at:

   http://localhost:8000


## Running the tests

The tests interact with the database so you'll first need to migrate
your test database. First, set the `DATABASE_URL_TEST` and then run:

```bash
$ createdb surveys_test
$ APP_ENV=testing php artisan migrate
```

Run at the top-level directory.

```bash
$ phpunit
```

If you don't have phpunit installed on your system, you can follow [these
instructions](https://phpunit.de/manual/current/en/installation.html) to
install it.

## Meta

* No warranty expressed or implied. Software is as is. Diggity.
* [MIT License](http://www.opensource.org/licenses/mit-license.html)
* Lovingly crafted by Twilio Developer Education.

#  Automated surveys using Laravel

[![Build Status](https://travis-ci.org/TwilioDevEd/automated-survey-laravel.svg?branch=master)](https://travis-ci.org/TwilioDevEd/automated-survey-laravel)

This application demostrates how to use Twilio and TwiML to perform
automatic phone surveys.

[Read the full tutorial](https://www.twilio.com/docs/tutorials/walkthrough/automated-survey/php/laravel)!

## Deploying to Heroku

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

## Running locally

### The web application

1. Clone the repository, copy the included `env.example` as `.env` and
   customize it to your needs. The application only requires a running database
2. Use composer to install the app's dependencies by running `composer
   install` in the repo's root
3. Run the database migrations using `php artisan migrate`. If the
   database is configured correctly in `.env` this will get you a
   working database
4. In order to run the application you will need at least one
   survey. The repository includes a sample survey you can use to test
   the application. Run `php artisan surveys:load bear_survey.json` to
   load a survey about bears into your database. If you have deployed
   to Heroku using the button you already have this survey loaded.

### Configuring Twilio to call your application

#### Exposing the app via ngrok

For this demo it's necessary that your local application instance is
accessible from the Internet. The easiest way to accomplish this
during development is using [ngrok](https://ngrok.com/). If you're
running OS X you can install ngrok using Homebrew by running `brew
install ngrok`. First you will need to run the application:

```
php artisan serve
```

After this you can expose the application to the wider Internet by
running (port 8000 is the default for Laravel):

```
ngrok 8000
```

#### Configuring Twilio's webhooks

You will need to provision at least one Twilio number with voice
capabilities so the application's users can take surveys. You can do
that
[here](https://www.twilio.com/user/account/phone-numbers/search). Once
you have a number you need to configure your number to work with your
application. Open
[the number management page](https://www.twilio.com/user/account/phone-numbers/incoming)
and open a number's configuration by clicking on it.

![Open a number configuration](https://raw.github.com/TwilioDevEd/automated-survey-laravel/master/number-conf.png)

Next, edit the "Request URL" field under the "Voice" section and point
it towards your ngrok-exposed application `/first_survey` route. Make
sure the method is set to POST. If you have are trying out the Heroku
application you need to point Twilio to
`http://<your-app-name>.herokuapp.com/first_survey`. See the image
below for an example:

![Webhook configuration](https://raw.github.com/TwilioDevEd/automated-survey-laravel/master/webhook-conf.png)

Give your number a call, answer the questions about bears and then go to:

```
http://localhost:8000/survey/1/results
```

The results of the survey should be there.

#### Running the tests

The tests interact with the database so you'll first need to migrate
your test database. First, set the `DATABASE_URL_TEST` and then run:

`php artisan migrate`

The tests then can be run using

`phpunit`

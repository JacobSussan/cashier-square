# Contribution Guide

The Laravel contributing guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Running Cashier's Tests

You will need to set the Square **testing** secret environment variable in a custom `phpunit.xml` file in order to run the Cashier tests.

Copy the default file using `cp phpunit.xml.dist phpunit.xml` and add the following line to the `<php>` section in your new `phpunit.xml` file:

    <env name="SQUARE_ACCESS_TOKEN" value="Your Square Testing Secret Key"/>

**Make sure to use your "testing" secret key and not your production secret key.** These tests may take a few minutes to run due to the fact that actual API requests against Square are being made. You may also encounter rate limiting errors from Square while running these tests. Unfortunately, there is no work around for this except waiting for the rate limit to expire.

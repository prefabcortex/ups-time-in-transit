# Time In Transit

## API Information

- **Title:** Time In Transit
- **Source Spec SHA-256:** f750da33d3691e2e9ee45f1e768b781f3bac96e44c12487028dd9fb6c3a92451

## API description

Quoted from the API description, without its formatting. The text is its author's and is
not covered by this package's licence — see [License](#license).

> The Time In Transit API provides estimated delivery times for various UPS shipping
> services, between specified locations.
>
> Key Business Values:\
> \- Enhanced Customer Experience: Allows businesses provide accurate delivery estimates
> to their customers, enhancing customer service.\
> \- Operational Efficiency: Helps in logistics planning by providing transit times for
> different UPS services.
>
> Reference
>
> \- Appendix\
> \- Errors
>
> Try out UPS APIs with example requests using Postman and learn more about the UPS
> Postman Collection by visiting our Postman Guide. Explore API documentation and
> sample applications through GitHub.

## Installation

```bash
composer require prefabcortex/ups-time-in-transit
```

### HTTP client

This package talks to the API over PSR-18 and does not ship an HTTP client of its
own, so it never forces a second one on a project that already has one. If yours has
none yet, the command above stops before installing anything and lists the packages
that qualify — pick one:

```bash
composer require guzzlehttp/guzzle
```

`symfony/http-client` works just as well, and either is picked up automatically. To
use a client you configured yourself, hand it to `ClientConfig::withHttpClient()`
before building the client.

A client picked up automatically gets a timeout — ten seconds to connect, sixty for the
whole request — which `ClientConfig::withTimeout(Timeout::seconds(…))` changes. A client
you hand over keeps the timeouts you gave it.

## Choosing a server

`ClientConfig::forServer()` takes one of the servers the API description names. Each is named
after what the description says of it, and their order means nothing: pick the one your
credentials belong to.

| Server | URL | Description |
|---|---|---|
| `Server::CustomerIntegrationEnvironment` | `https://wwwcie.ups.com/api` | Customer Integration Environment |
| `Server::Production` | `https://onlinetools.ups.com/api` | Production |

```php
use Prefabcortex\UpsTimeInTransit\ClientConfig;
use Prefabcortex\UpsTimeInTransit\Server;

$config = ClientConfig::forServer(Server::CustomerIntegrationEnvironment);
```

For a mock server or a proxy, `ClientConfig::forBaseUrl('https://mock.example.com')` sends
every request to one address.

## Quickstart

Every link below points at a file under `examples/` to read and copy from.

### Authentication

- **OAuth2** (OAuth2): `Client::withOAuth($token, $config)` — see [`examples/Auth/OAuth2.php`](examples/Auth/OAuth2.php)

For a scheme this package generates no authenticator for, or a signature the description cannot express, implement `Authentication\Authenticator` and pass it to `Client::withAuthenticators($config, ...$authenticators)`.

### Operations

#### General

- [`timeInTransit`](examples/Operations/TimeInTransitExample.php)

## Validation is lax by default

**An API description is a claim about an API, and it is wrong more often than you would
expect.** A code is declared three characters long while every documented value has two.
A weight carries a maximum of `1` that was never meant as a limit. A response field runs
one character past its declared length, or arrives as `null` where a value was promised.
None of this is rare, and none of it is yours to fix.

So this package does **not** hold what it sends or what it reads to the constraints of
the description — maximum and minimum lengths, patterns, ranges, formats, item counts.
A request is sent as you built it; a response is read as far as its model can hold it.
This is `ValidationMode::Lax`, and it is what you get without asking.

What the PHP types say holds either way: a property has its declared type, a required
property is there, and a request names only the cases an enum has.

### If the API keeps to its description

Then have every constraint checked, in both directions:

```php
use Prefabcortex\UpsTimeInTransit\Http\ValidationMode;

$config = $config->withValidation(ValidationMode::Strict);
```

A request that breaks a constraint then raises `ValidationException` and is never sent,
so a mistake in your data surfaces before the API sees it. A response that breaks one
raises `ResponseValidationException`, so a difference between the API and its description
shows up the first time it happens, not somewhere further down your code. If either one
turns out to be the description's mistake rather than yours, go back to `Lax`.

|                                              | `Lax` (default)              | `Strict`                      |
|----------------------------------------------|------------------------------|-------------------------------|
| **A request carries**                        |                              |                               |
| a text too long, a pattern or range not met  | sent as it is                | `ValidationException`         |
| **A response carries**                       |                              |                               |
| a text too long, a pattern or range not met  | read as it is                | `ResponseValidationException` |
| `null` in an optional property               | read as absent               | `ResponseValidationException` |
| a value its model cannot hold                | `MalformedResponseException` | `ResponseValidationException` |

A value its model cannot hold — a wrong type, a missing required property — stays an
error either way, because a typed model has nowhere to put it.

`ValidationException` means nothing was sent. The two response exceptions are
`ResponseException`s, so `getResponse()` and `getRawResponse()` hand back what arrived.
Mind what that means: **the request reached the API**, and whatever it asked for may have
happened — an order placed, a payment taken. Do not simply send it again; read the raw
response first.

## Optional values

A property the description marks required is a plain type on the model. A property it
leaves optional is an `Option`, because there are three states to tell apart and not
two: the field was absent, the field was `null`, or the field had a value. A `?string`
can hold the last two and loses the first, and losing it matters — `toArray()` sends
back only what was actually set, so a field the server never mentioned stays unmentioned
rather than being echoed as `null`.

Three methods, and no others:

```php
$value->isDefined();        // was it there at all?
$value->get();              // the value — RuntimeException on an absent one
$value->getOrElse($other);  // the value, or $other when it is absent
```

On `CandidateAddress`, for instance, `getCountryName()` is required and reads as
itself, while `getCountryCode()` is optional:

```php
$model->getCountryName();                 // the value
$model->getCountryCode()->getOrElse('');  // the value, or a default
```

Writing one is the other way round, and needs none of this. `builder()` takes the required
properties and starts every optional one absent; each `set…()` takes the bare value and
wraps it for you, and `build()` hands back the model. `toBuilder()` starts from a model you
already have, which itself never changes. Query and header parameter objects take bare
values in their `with…()` the same way. There is no reason to write `Some::create()` or
`None::create()` yourself.

## Error handling

Every exception a call can end in implements `ApiException`, so one catch covers
everything the API can report:

```php
try {
    // … any operation
} catch (ApiException $e) {
    // …
}
```

That includes the call never completing. A failure your HTTP client reports — a
timeout, a refused connection, a request it would not send — is caught and re-thrown as
`TransportException`, so it lands in the same catch as everything else rather than
beside it.

Two things follow, and both are deliberate. The PSR interfaces still match, so
`catch (Psr\Http\Client\NetworkExceptionInterface $e)` before the block above still
picks out the failures worth retrying, and `getRequest()` tells you what was being
sent. But a catch written against your client's *own* class — Guzzle's
`ConnectException`, say — does not match, because the exception you receive is not that
class. The original is there as `getPrevious()`.

One exception stays outside the catch on purpose, because it is a mistake in the
calling code rather than a way the call can fail: `get()` on an `Option` that holds no
value throws `RuntimeException`. Ask `isDefined()` first, or use `getOrElse()`.

Below it the hierarchy narrows:

- `ResponseException` — raised by a response that arrived, and where
  `getResponse()` and `getRawResponse()` are declared. `MalformedDataException`,
  `NoHttpClientException`, `UnsupportedValueException` and `ValidationException` have
  none to hand back and stop at `ApiException`.
- `ClientException` — every status below is a client error; this API declares no 5xx.
- One class per status: `BadRequestException`, `UnauthorizedException`,
  `ForbiddenException`, `TooManyRequestsException`.
- One class per operation and status — these are the ones actually thrown. Each adds the
  typed error body:
  `TimeInTransitBadRequestException::getErrorResponse(): ErrorResponse`.

A response that fits no declared branch is one of two `ResponseException`s with nothing
beyond the two accessors, named after what did not fit:

- `UnexpectedStatusCodeException` — no response declares the status, or a declared error
  response's body does not read as declared (the reason is `getPrevious()`). Its status
  still says which side failed: a 4xx is a `ClientException`, a 5xx a `ServerException`.
- `UnexpectedContentTypeException` — the status is declared, but not under the content
  type the response arrived with. A 404 served as `text/html` by a proxy in front of the
  API is this, not a `NotFoundException`: its body is not the one the description
  promises, so there is nothing to read it into.

Two things an error response carries are read from any `ResponseException`, whether or
not the description declares its status. A 429 or a 503 often says how long to wait in
a `Retry-After` header, as a number of seconds or as a date; `RetryAfter` reads either.
A body served as `application/problem+json` (RFC 9457) reads as `ProblemDetails`:

```php
use Prefabcortex\UpsTimeInTransit\Exception\ClientException;
use Prefabcortex\UpsTimeInTransit\Http\ProblemDetails;
use Prefabcortex\UpsTimeInTransit\Http\RetryAfter;

try {
    // … any operation
} catch (ClientException $e) {
    $problem = ProblemDetails::of($e);
    if ($problem->isDefined()) {
        error_log($problem->get()->detail->getOrElse($e->getMessage()));
    }

    $retryAfter = RetryAfter::of($e);
    if ($retryAfter->isDefined()) {
        sleep($retryAfter->get()->secondsFrom(new DateTimeImmutable()));
    }
}
```

## Versioning

This package carries its own SemVer line. The API version shown above is *provenance*,
not the package version — it is recorded in `.prefabcortex-generation.json` along with
the checksum of the specification it was generated from.

How far a release counts is measured, not chosen:
`roave/backward-compatibility-check` compares it against the tag before it.

| The check reports | Release |
| --- | --- |
| a break in the public API | **major** — `2.4.1` becomes `3.0.0` |
| no break | **minor** — `2.4.1` becomes `2.5.0` |

There are no patch releases. The check finds breaks, not additions, so anything that
changed without breaking is released as a minor — and a constraint such as `^2.4` never
installs a break.

Symbols marked `@internal` are excluded from all of this. They are the package's
plumbing — the transport classes under `Http/` and `Operation/`, the `to…Parameters()`
conversions — and they may change in any release. Everything else is the contract,
including what code outside the package implements or calls: the validation rules and
the types a `QueryParameterTransformer` works with.

## Trademarks

Trademarks mentioned here — including in the name of this package — are the
property of their respective owners. They appear to identify the API this client
addresses, and for no other purpose: nothing here is a claim about who made this
package or who stands behind it.

This is an unofficial client, generated from the published API description. It IS
NOT affiliated with, endorsed by, or connected to the operator of that API.

## License

The generated code is 0BSD — see [LICENSE](LICENSE). Documentation text carried over
from the API description is quoted from that description and remains its author's;
it is reproduced here to document the interface, not relicensed. Holding the rights to
that description is the responsibility of this package's publisher, the copyright
holder named in [LICENSE](LICENSE).

## About This Package

This package was generated from an OpenAPI specification. It is yours to take
further — bear in mind only that regenerating it replaces every file in the
package, so anything changed by hand is worth keeping somewhere that survives
that: a patch, a subclass, or a fork you maintain yourself.

Generated by [PrefabCortex](https://www.prefabcortex.com), which turns an OpenAPI
specification into a ready-to-use PHP Composer package. PrefabCortex
generated it on behalf of the publisher named in [LICENSE](LICENSE) and
neither publishes, reviews nor maintains it.

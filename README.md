# rnd.mtex.dev

**Random & Fake Data API — an [MTEX.dev](https://mtex.dev) service**

> Instant random data for developers. No auth. No API key. No rate limits. Just GET requests.

Built with [php-zero](https://gh.mtex.dev/php-zero) — the lightweight PHP 8.1+ micro-framework by MTEX.dev.

---

## What is rnd?

`rnd.mtex.dev` is a developer utility API for generating random and fake data on demand. Useful for:

- Populating databases with test data
- Mocking API responses
- Seeding frontend prototypes
- Games, simulations, and random-driven logic
- Teaching and demos

---

## Base URL

```
https://rnd.mtex.dev/api
```

All endpoints respond with `application/json`. All requests are `GET`. No authentication required.

---

## Endpoints

### Meta

| Endpoint | Description |
|---|---|
| `GET /ping` | Health check — returns service info and current timestamp |
| `GET /endpoints` | Full machine-readable list of all endpoints and their parameters |

---

### `GET /api/uuid`

Generate UUID v4 values.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of UUIDs to generate (max 100) |

**Example**

```
GET /api/uuid?count=3
```

```json
{
  "count": 3,
  "results": [
    { "uuid": "f47ac10b-58cc-4372-a567-0e02b2c3d479" },
    { "uuid": "3d6f4440-3e5b-41ab-90d5-e7a4eb4c5b32" },
    { "uuid": "9b2d6f8a-1c4e-4a2b-b3d5-7f8e9a0c1d2e" }
  ]
}
```

---

### `GET /api/name`

Random person name (first + last).

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of names (max 50) |
| `gender` | string | `any` | `male`, `female`, or `any` |

**Example**

```
GET /api/name?gender=female
```

```json
{
  "first": "Aria",
  "last": "Walker",
  "full": "Aria Walker",
  "gender": "female"
}
```

---

### `GET /api/email`

Random email address.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of emails (max 50) |
| `domain` | string | *(random)* | Force a specific email domain |

**Example**

```
GET /api/email?domain=example.com&count=2
```

---

### `GET /api/color`

Random color in one or all formats.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of colors (max 50) |
| `format` | string | `all` | `hex`, `rgb`, `hsl`, or `all` |

**Example**

```
GET /api/color?format=hex&count=5
```

```json
{
  "count": 5,
  "results": [
    { "hex": "#a34fc2" },
    { "hex": "#1d9a6c" },
    ...
  ]
}
```

---

### `GET /api/gradient`

Random CSS gradient string, ready to drop into a stylesheet.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of gradients (max 20) |
| `type` | string | `linear` | `linear`, `radial`, or `conic` |

**Example**

```
GET /api/gradient?type=linear
```

```json
{
  "css": "linear-gradient(217deg, #4fa2c2, #f29e2b, #7c3aed)",
  "type": "linear",
  "angle": 217,
  "colors": ["#4fa2c2", "#f29e2b", "#7c3aed"]
}
```

---

### `GET /api/number`

Random integer or float.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | How many numbers (max 100) |
| `min` | number | `0` | Lower bound (inclusive) |
| `max` | number | `100` | Upper bound (inclusive) |
| `float` | bool | `false` | Return decimal instead of integer |

**Example**

```
GET /api/number?min=1&max=6&count=4
```

---

### `GET /api/string`

Random string from a configurable character pool.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of strings (max 50) |
| `length` | int | `16` | String length (max 512) |
| `charset` | string | `alphanum` | `alpha`, `alphanum`, `hex`, `numeric`, `symbols`, `all` |

**Example**

```
GET /api/string?length=32&charset=hex
```

---

### `GET /api/lorem`

Lorem ipsum placeholder text.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of result blocks (max 20) |
| `type` | string | `sentences` | `words`, `sentences`, or `paragraphs` |
| `count` (inner) | int | `3` | Number of words/sentences/paragraphs per result |

**Example**

```
GET /api/lorem?type=paragraphs&count=2
```

---

### `GET /api/ip`

Random IP address (IPv4 and/or IPv6).

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of addresses (max 50) |
| `type` | string | `v4` | `v4`, `v6`, or `both` |

---

### `GET /api/date`

Random date within a configurable range.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of dates (max 50) |
| `from` | string | `1970-01-01` | Start of range (any PHP-parseable date) |
| `to` | string | today | End of range |
| `format` | string | `Y-m-d` | PHP `date()` format string |

**Example**

```
GET /api/date?from=2000-01-01&to=2023-12-31&format=d/m/Y
```

---

### `GET /api/pick`

Pick random item(s) from a comma-separated list you provide.

**Parameters**

| Param | Type | Required | Description |
|---|---|---|---|
| `items` | string | ✅ | Comma-separated list of options |
| `count` | int | | How many to pick (default 1) |
| `unique` | bool | | If `true`, never repeat an item |

**Example**

```
GET /api/pick?items=rock,paper,scissors&count=1
```

---

### `GET /api/roll`

Roll dice using standard RPG notation (`NdS`, `NdS+M`).

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `dice` | string | `1d6` | Notation: `2d6`, `4d8+2`, `1d100`, etc. |
| `count` | int | `1` | Number of roll sets (max 20) |

**Example**

```
GET /api/roll?dice=2d20+5&count=3
```

```json
{
  "count": 3,
  "results": [
    { "result": 27, "dice": [14, 8], "modifier": 5, "notation": "2d20+5" },
    ...
  ]
}
```

---

### `GET /api/coin`

Flip a coin.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of flips (max 100) |

**Example**

```
GET /api/coin?count=10
```

---

### `GET /api/hash`

Hash a string with a common algorithm.

**Parameters**

| Param | Type | Required | Description |
|---|---|---|---|
| `value` | string | ✅ | Input string to hash |
| `algo` | string | | `md5`, `sha1`, `sha256` (default), `sha512` |

**Example**

```
GET /api/hash?value=hello+world&algo=sha256
```

---

### `GET /api/password`

Generate a cryptographically random password.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `count` | int | `1` | Number of passwords (max 20) |
| `length` | int | `16` | Password length (8–128) |
| `symbols` | bool | `true` | Include special characters |

Response includes an **entropy estimate in bits** alongside the password.

---

### `GET /api/avatar`

Generate a deterministic SVG avatar as a data URI — same `seed` always yields the same image.

**Parameters**

| Param | Type | Default | Description |
|---|---|---|---|
| `seed` | string | *(random)* | Seed string — same seed → same avatar |
| `size` | int | `80` | Width/height in px (32–512) |
| `style` | string | `geometric` | `geometric`, `pixel`, or `initials` |

**Example**

```
GET /api/avatar?seed=john-doe&size=128&style=geometric
```

Response contains both `data_uri` (ready for `<img src="...">`) and raw `svg` markup.

---

## Multiple Results

Every endpoint that supports `count` returns a consistent envelope when `count > 1`:

```json
{
  "count": 3,
  "results": [ ... ]
}
```

When `count=1` (default), the result object is returned directly — no wrapping array.

---

## Response Headers

Every response includes:

| Header | Description |
|---|---|
| `Content-Type` | `application/json; charset=utf-8` |
| `Cache-Control` | `no-store, no-cache, must-revalidate` |
| `X-RND-Count` | Number of items generated |

---

## Error Responses

| Status | Meaning |
|---|---|
| `422` | Missing or invalid parameter |
| `404` | Unknown endpoint |
| `500` | Server error |

```json
{
  "error": "Missing required param: items (comma-separated list)"
}
```

---

## Deployment

### Requirements

- PHP **8.1+**
- Apache (`mod_rewrite`) or Nginx
- [php-zero](https://gh.mtex.dev/php-zero) framework

### Setup

```bash
# 1. Clone php-zero and this service
git clone https://gh.mtex.dev/php-zero.git rnd.mtex.dev
cd rnd.mtex.dev

# 2. Drop the rnd.mtex.dev application files into the project
cp -r rnd-service/* .

# 3. Configure environment
cp .env.example .env

# 4. Point web server root at /public
```

### Nginx

```nginx
server {
    server_name rnd.mtex.dev;
    root /var/www/rnd.mtex.dev/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Other MTEX.dev Services

| Service | Description |
|---|---|
| [status.mtex.dev](https://status.mtex.dev) | System status page |
| [tw.mtex.dev](https://tw.mtex.dev) | Tailwind Component Library |
| [nx.mtex.dev](https://nx.mtex.dev) | MTEX Nexus — simple, playful API |
| [http.mtex.dev](https://http.mtex.dev) | Web-based HTTP client |
| **[rnd.mtex.dev](https://rnd.mtex.dev)** | **Random & Fake Data API** ← you are here |

---

## License

MIT License — © [MTEX.dev](https://mtex.dev)

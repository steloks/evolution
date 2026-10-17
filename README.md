# Clean Air Finder

Pick a period and see which Bulgarian city had the cleanest air, ranked by average PM2.5.

## Running it

```sh
docker compose up
```

Open http://localhost:8000.

On the first start the app downloads the last 6 months from OpenAQ. That takes about 15 minutes, and the ranking fills up city by city while it runs. After that it only fetches new hours.

The OpenAQ API key and the app key are written straight into `compose.yaml` so the project starts with one command. In a real project they would be secrets.

## How it works

Four containers:

- `pgsql` - Postgres 17, the data lives in a volume.
- `tests` - runs the test suite first. The app does not start if a test fails.
- `app` - runs the migrations and the city seeder, then serves the page and the API.
- `scheduler` - imports sensors and measurements once on start, then runs the Laravel scheduler:
  - `app:import-sensors` every day at 00:00 UTC
  - `app:import-measurements` every hour at :05

User requests only read from our database. OpenAQ is never called while serving a page.

### Data

- `cities` - the 27 Bulgarian province capitals with coordinates and a radius (4-12 km depending on population).
- `sensors` - OpenAQ PM2.5 sensors in Bulgaria. A sensor belongs to a city if it is inside the city's radius.
- `measurements` - one row per sensor per hour, the hourly average from OpenAQ. Hours are stored in UTC.

Each sensor has `imported_until`, the point the import got to. The next run continues from there, so if the app was down for a month it fetches that month on the next run.

### Import

- Six months back for a new sensor, in 30-day requests. Longer ranges make OpenAQ time out (408).
- On every run it goes 3 hours back from `imported_until`, because OpenAQ publishes some hours late.
- One second pause before each request because of the 60 requests/minute limit, plus 3 retries.
- Values that are missing, negative or without a time are skipped.
- If one sensor fails, it is logged and the others continue. Its `imported_until` stays where it was, so the next run picks it up.

## API

`GET /api/rankings?from=2026-07-01T00:00&to=2026-07-08T00:00`

```json
{
  "hours": 168,
  "query_ms": 6.1,
  "cities": [
    {"name": "Габрово", "pm25": 5.1, "sensors": 16, "coverage": 99}
  ]
}
```

- `from` and `to` are Bulgarian local time, whole hours. `to` is not included.
- Cities are sorted from cleanest to dirtiest.
- `coverage` is the share of hours in the period where the city has at least one measurement.
- Invalid input returns 422 with a message per field.

Other endpoints:

- `GET /api/health` - `ok`, `stale` (no new data for 6 hours) or `down` with 503 when the database is not reachable. Also shows the last import.
- `GET /api/metrics` - Prometheus text format: sensors, measurements, last import (time, duration, stored, failed sensors), ranking requests and total query time.

Logs go to stderr: `docker compose logs -f scheduler`.

## Speed

The ranking is one SQL query over `measurements`, filtered by the `hour` index and grouped by city.

Measured with `EXPLAIN ANALYZE` on the real data (138k rows, 48 sensors with data):

| Period  | Rows read | Time    |
|---------|-----------|---------|
| 1 hour  | 36        | 0.9 ms  |
| 1 day   | 860       | 2.3 ms  |
| 1 week  | 5,926     | 5.9 ms  |
| 1 month | 25,243    | 22 ms   |
| 6 months| ~138,000  | 75-110 ms |

Every response also has `query_ms`, and it is shown under the table in the UI.

Time grows with the length of the period. It is fine for this amount of data, but with years of history it would not be. See improvements.

## Data source

[OpenAQ v3](https://docs.openaq.org). `/locations?iso=BG` for the sensors and `/sensors/{id}/hours` for hourly averages.

I also looked at Sensor.Community (covers more cities, but the archive started blocking me after a few parallel downloads) and the EEA download service (only 4 PM2.5 stations in Bulgaria). OpenAQ was the most reliable.

## Assumptions

- A city's average is the mean of all its sensor-hours in the period.
- Only 10 of the 27 cities have PM2.5 sensors in OpenAQ. Burgas has none, so it is not in the ranking.
- Data is never deleted, so the history keeps growing beyond 6 months.
- The latest 1-2 hours may be missing because OpenAQ publishes them late.

## Trade-offs

- Hourly averages only, no raw readings. Hours are the smallest unit a user can pick, so this is enough.
- No daily or monthly rollup yet. Simpler and fast enough now.
- Tests run on SQLite in memory so they do not need Docker. Production is Postgres.

## Improvements

- Daily table with sum and count per city and day. Whole days come from it, only the edge hours from `measurements`. Query time would stay almost the same for any period length.
- Partition `measurements` by month once it gets big.
- Cache rankings until the next import.
- More data sources to cover the missing cities.

## AI usage

I used Claude Code as a pair programmer. I made the decisions and reviewed every change.

What I decided myself:

- The stack: Laravel, Vue with Quasar, Postgres, the Laravel scheduler.
- The data source. We tried Sensor.Community and the EEA service first, and I picked OpenAQ because it was the most reliable, even though it means no Burgas.
- Storing hourly averages instead of raw readings, and keeping the ranking on the hourly table instead of adding a rollup before it was needed.
- Splitting the sensor import (daily) from the measurement import (hourly).

Where AI helped:

- Exploring the data sources: checking which Bulgarian cities each API covers, finding the OpenAQ limits (60 requests/minute, timeouts on long ranges) and working around them.
- Matching sensors to cities by coordinates: the haversine distance and the city radius by population.
- Checking the results: comparing stored data with OpenAQ, measuring the query times.


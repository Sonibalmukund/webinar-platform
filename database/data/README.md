# Worldwide location data

`world-locations.json.gz` is sourced from the [Countries States Cities Database](https://github.com/dr5hn/countries-states-cities-database) and is distributed under the Open Database License (ODbL) 1.0.

The `LocationSeeder` imports the compressed dataset into the local `countries`, `states`, and `cities` tables. The application does not make runtime requests to an external location API.

# Open Policy Agent Policy Generator

Convert an [OpenAPI](https://www.openapis.org/) 3 specification to an OPA policy.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.txt)

## Warning
While this tool will produce a file representing a policy, it is advised a manual review of every line is performed prior to using the policy.

Items such as API-Keys must be manually set, and any other security related items not definable in a spec.

## Early Development
This tool is under development and may not support all OpenAPI3 features, please open feature requests if you find features missing that you require.

## How to use

The easiest way to use opapg is via docker, mounting a folder with a spec in as a volume
```bash
    docker run --volume d:/projects/myapi:/srv/app segrax/opapg from-openapi openapi.yaml --output=mypolicy
```

This will produce a policy and a set of tests for the policy (in the mounted volume) and will output the results of an 'opa test' command to the console

```json
data.name.api.test_locat_id_oauth2_allowed: PASS (537.7µs)
data.name.api.test_locat_id_oauth2_denied: PASS (401.6µs)
data.name.api.test_locat_id_apiKey1_allowed: PASS (352.5µs)
data.name.api.test_locat_id_apiKey1_denied: PASS (293.5µs)
data.name.api.test_locations_near_allowed: PASS (280.2µs)
data.name.api.test_locations_get_allowed: PASS (849.6µs)
data.name.api.test_locations_create_bearerAuth_allowed: PASS (707.1µs)
data.name.api.test_locations_create_bearerAuth_denied: PASS (346.1µs)
data.name.api.test_media_get_allowed: PASS (398µs)
data.name.api.test_media_list_allowed: PASS (343.1µs)
data.name.api.test_media_upload_bearerAuth_allowed: PASS (314.3µs)
data.name.api.test_media_upload_bearerAuth_denied: PASS (447.2µs)
--------------------------------------------------------------------------------
PASS: 12/12
```

## Todo

Lots of features remain to be added, please submit ideas/feature requests to the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.

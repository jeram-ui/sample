---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://localhost/docs/collection.json)

<!-- END_INFO -->

#general


<!-- START_f7b7ea397f8939c8bb93e6cab64603ce -->
## Display Swagger API page.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/documentation" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/documentation"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET api/documentation`


<!-- END_f7b7ea397f8939c8bb93e6cab64603ce -->

<!-- START_1ead214f30a5e235e7140eb2aaa29eee -->
## Dump api-docs content endpoint. Supports dumping a json, or yaml file.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/docs/" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/docs/"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "UNLAD LGIS",
        "description": "UNLAD LGIS API",
        "contact": {
            "email": "darius@matulionis.lt"
        },
        "license": {
            "name": "Apache 2.0",
            "url": "http:\/\/www.apache.org\/licenses\/LICENSE-2.0.html"
        },
        "version": "1.0.0"
    },
    "servers": [
        {
            "url": "http:\/\/localhost:80\/LGU_BACK\/public\/api",
            "description": "L5 Swagger OpenApi dynamic host server"
        },
        {
            "url": "http:\/\/localhost:80\/LGU_BACK\/public",
            "description": "L5 Swagger OpenApi Server"
        }
    ],
    "paths": {
        "\/General\/Business\/getBusinessStatus": {
            "get": {
                "tags": [
                    "General Data"
                ],
                "summary": "Business Status",
                "operationId": "getBusinessStatus",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/General\/Business\/getBusinessType": {
            "get": {
                "tags": [
                    "General Data"
                ],
                "summary": "Type of Business",
                "operationId": "getBusinessType",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/General\/Business\/getBusinessKind": {
            "get": {
                "tags": [
                    "General Data"
                ],
                "summary": "Kind of Businesses",
                "operationId": "getBusinessKind",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/General\/Business\/getofficeType": {
            "get": {
                "tags": [
                    "General Data"
                ],
                "summary": "Office Types",
                "operationId": "getofficeType",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/General\/Business\/getBSPType": {
            "get": {
                "tags": [
                    "General Data"
                ],
                "summary": "BSP Types",
                "operationId": "getBSPType",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/General\/Others\/getAlhabeticalFilter": {
            "get": {
                "tags": [
                    "General Data"
                ],
                "summary": "Alphabetical Letter",
                "operationId": "getAlhabeticalFilter",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/globalVariable": {
            "get": {
                "tags": [
                    "Global Data"
                ],
                "summary": "Global Variables",
                "description": "List of Global Data with corresponding Variables-> Department:department, Employees Department:employeeDepartment, Source of Fund:SOF, employee:Employees, Barangay:barangay, Revision Years:revisionyears, Cashier:cashier. Note use the map getters to retrieve this data ",
                "operationId": "globalVariable",
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        },
        "\/Treasury\/RealPropertyTax\/getdelinquency": {
            "post": {
                "tags": [
                    "Treasury - Reports"
                ],
                "summary": "Real Property Delinquency",
                "operationId": "getdelinquency",
                "requestBody": {
                    "description": "Data required to create a case",
                    "required": true,
                    "content": {
                        "application\/json": {
                            "schema": {
                                "properties": {
                                    "main": {
                                        "type": "array",
                                        "items": {
                                            "properties": {
                                                "abc": {
                                                    "type": "string"
                                                },
                                                "brgyId": {
                                                    "type": "string"
                                                },
                                                "year": {
                                                    "type": "string"
                                                },
                                                "revision": {
                                                    "type": "string"
                                                },
                                                "blDisabledUnpaid": {
                                                    "type": "string"
                                                }
                                            },
                                            "type": "object"
                                        },
                                        "collectionFormat": "multi"
                                    }
                                },
                                "type": "object",
                                "collectionFormat": "multi"
                            }
                        }
                    }
                },
                "responses": {
                    "201": {
                        "description": "Null response"
                    },
                    "default": {
                        "description": "unexpected error"
                    }
                }
            }
        }
    },
    "components": {
        "securitySchemes": {
            "api_key": {
                "type": "apiKey",
                "name": "api_key",
                "in": "header"
            },
            "petstore_auth": {
                "type": "oauth2",
                "flows": {
                    "implicit": {
                        "authorizationUrl": "http:\/\/petstore.swagger.io\/oauth\/dialog",
                        "scopes": {
                            "read:pets": "read your pets",
                            "write:pets": "modify pets in your account"
                        }
                    }
                }
            }
        }
    },
    "security": [
        {
            "oauth2": [
                "read:oauth2"
            ]
        }
    ]
}
```

### HTTP Request
`GET docs/{jsonFile?}`

`POST docs/{jsonFile?}`

`PUT docs/{jsonFile?}`

`PATCH docs/{jsonFile?}`

`DELETE docs/{jsonFile?}`

`OPTIONS docs/{jsonFile?}`


<!-- END_1ead214f30a5e235e7140eb2aaa29eee -->

<!-- START_1a23c1337818a4de9e417863aebaca33 -->
## docs/asset/{asset}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/docs/asset/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/docs/asset/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (404):

```json
{
    "message": "(1) - this L5 Swagger asset is not allowed"
}
```

### HTTP Request
`GET docs/asset/{asset}`


<!-- END_1a23c1337818a4de9e417863aebaca33 -->

<!-- START_a2c4ea37605c6d2e3c93b7269030af0a -->
## Display Oauth2 callback pages.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/oauth2-callback" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/oauth2-callback"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET api/oauth2-callback`


<!-- END_a2c4ea37605c6d2e3c93b7269030af0a -->

<!-- START_f4e562e1284a234a9d2d092a548a7b6f -->
## Return an empty response simply to trigger the storage of the CSRF cookie in the browser.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/sanctum/csrf-cookie" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/sanctum/csrf-cookie"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/sanctum/csrf-cookie`


<!-- END_f4e562e1284a234a9d2d092a548a7b6f -->

<!-- START_dce9dd14c59fdead2899c4ff28bc7402 -->
## openapi
> Example request:

```bash
curl -X GET \
    -G "http://localhost/openapi" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/openapi"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET openapi`


<!-- END_dce9dd14c59fdead2899c4ff28bc7402 -->

<!-- START_0c068b4037fb2e47e71bd44bd36e3e2a -->
## Authorize a client to access the user&#039;s account.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/oauth/authorize" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/authorize"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET oauth/authorize`


<!-- END_0c068b4037fb2e47e71bd44bd36e3e2a -->

<!-- START_e48cc6a0b45dd21b7076ab2c03908687 -->
## Approve the authorization request.

> Example request:

```bash
curl -X POST \
    "http://localhost/oauth/authorize" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/authorize"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST oauth/authorize`


<!-- END_e48cc6a0b45dd21b7076ab2c03908687 -->

<!-- START_de5d7581ef1275fce2a229b6b6eaad9c -->
## Deny the authorization request.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/oauth/authorize" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/authorize"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE oauth/authorize`


<!-- END_de5d7581ef1275fce2a229b6b6eaad9c -->

<!-- START_a09d20357336aa979ecd8e3972ac9168 -->
## Authorize a client to access the user&#039;s account.

> Example request:

```bash
curl -X POST \
    "http://localhost/oauth/token" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/token"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST oauth/token`


<!-- END_a09d20357336aa979ecd8e3972ac9168 -->

<!-- START_d6a56149547e03307199e39e03e12d1c -->
## Get all of the authorized tokens for the authenticated user.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/oauth/tokens" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/tokens"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET oauth/tokens`


<!-- END_d6a56149547e03307199e39e03e12d1c -->

<!-- START_a9a802c25737cca5324125e5f60b72a5 -->
## Delete the given token.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/oauth/tokens/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/tokens/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE oauth/tokens/{token_id}`


<!-- END_a9a802c25737cca5324125e5f60b72a5 -->

<!-- START_abe905e69f5d002aa7d26f433676d623 -->
## Get a fresh transient token cookie for the authenticated user.

> Example request:

```bash
curl -X POST \
    "http://localhost/oauth/token/refresh" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/token/refresh"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST oauth/token/refresh`


<!-- END_abe905e69f5d002aa7d26f433676d623 -->

<!-- START_babcfe12d87b8708f5985e9d39ba8f2c -->
## Get all of the clients for the authenticated user.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/oauth/clients" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/clients"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET oauth/clients`


<!-- END_babcfe12d87b8708f5985e9d39ba8f2c -->

<!-- START_9eabf8d6e4ab449c24c503fcb42fba82 -->
## Store a new client.

> Example request:

```bash
curl -X POST \
    "http://localhost/oauth/clients" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/clients"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST oauth/clients`


<!-- END_9eabf8d6e4ab449c24c503fcb42fba82 -->

<!-- START_784aec390a455073fc7464335c1defa1 -->
## Update the given client.

> Example request:

```bash
curl -X PUT \
    "http://localhost/oauth/clients/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/clients/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT oauth/clients/{client_id}`


<!-- END_784aec390a455073fc7464335c1defa1 -->

<!-- START_1f65a511dd86ba0541d7ba13ca57e364 -->
## Delete the given client.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/oauth/clients/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/clients/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE oauth/clients/{client_id}`


<!-- END_1f65a511dd86ba0541d7ba13ca57e364 -->

<!-- START_9e281bd3a1eb1d9eb63190c8effb607c -->
## Get all of the available scopes for the application.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/oauth/scopes" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/scopes"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET oauth/scopes`


<!-- END_9e281bd3a1eb1d9eb63190c8effb607c -->

<!-- START_9b2a7699ce6214a79e0fd8107f8b1c9e -->
## Get all of the personal access tokens for the authenticated user.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/oauth/personal-access-tokens" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/personal-access-tokens"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET oauth/personal-access-tokens`


<!-- END_9b2a7699ce6214a79e0fd8107f8b1c9e -->

<!-- START_a8dd9c0a5583742e671711f9bb3ee406 -->
## Create a new personal access token for the user.

> Example request:

```bash
curl -X POST \
    "http://localhost/oauth/personal-access-tokens" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/personal-access-tokens"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST oauth/personal-access-tokens`


<!-- END_a8dd9c0a5583742e671711f9bb3ee406 -->

<!-- START_bae65df80fd9d72a01439241a9ea20d0 -->
## Delete the given token.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/oauth/personal-access-tokens/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/oauth/personal-access-tokens/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE oauth/personal-access-tokens/{token_id}`


<!-- END_bae65df80fd9d72a01439241a9ea20d0 -->

<!-- START_a925a8d22b3615f12fca79456d286859 -->
## api/auth/login
> Example request:

```bash
curl -X POST \
    "http://localhost/api/auth/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/auth/login"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/auth/login`


<!-- END_a925a8d22b3615f12fca79456d286859 -->

<!-- START_3e4a08674c3c1aaa7a4e8aacbf86420a -->
## api/email/verify/{id}/{hash}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/email/verify/1/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/email/verify/1/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (302):

```json
null
```

### HTTP Request
`GET api/email/verify/{id}/{hash}`


<!-- END_3e4a08674c3c1aaa7a4e8aacbf86420a -->

<!-- START_f0c2f25541cb336092e2103a0692c168 -->
## api/storeRegister
> Example request:

```bash
curl -X POST \
    "http://localhost/api/storeRegister" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/storeRegister"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/storeRegister`


<!-- END_f0c2f25541cb336092e2103a0692c168 -->

<!-- START_f9ea878e50b2a5e10b8363f09b113b70 -->
## api/sendToken
> Example request:

```bash
curl -X POST \
    "http://localhost/api/sendToken" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/sendToken"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sendToken`


<!-- END_f9ea878e50b2a5e10b8363f09b113b70 -->

<!-- START_720312d582059126123886b5f104718d -->
## api/tokenValidate
> Example request:

```bash
curl -X POST \
    "http://localhost/api/tokenValidate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/tokenValidate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/tokenValidate`


<!-- END_720312d582059126123886b5f104718d -->

<!-- START_b4f4625b609a18310a50b1dddf752a55 -->
## api/resetPassword
> Example request:

```bash
curl -X POST \
    "http://localhost/api/resetPassword" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/resetPassword"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/resetPassword`


<!-- END_b4f4625b609a18310a50b1dddf752a55 -->

<!-- START_72eb12a87810aaa3ae5730a52f39524a -->
## api/ranz/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/ranz/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/ranz/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/ranz/store`


<!-- END_72eb12a87810aaa3ae5730a52f39524a -->

<!-- START_997f9b041a1e4f283a96e811ff972e2b -->
## api/ranz/show
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/ranz/show" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/ranz/show"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [
        {
            "id": "1",
            "trans_date": "2020-04-23",
            "trans_time": "10:23:15",
            "trans_combo": "1",
            "trans_text": "Bond Paper - Short",
            "radio": null,
            "checkbox": "",
            "trans_number": null,
            "trans_id": "0",
            "trans_desc": null,
            "status": "Active",
            "updated_at": "2020-04-23 02:23:48",
            "created_at": "2020-04-23 02:23:48"
        },
        {
            "id": "2",
            "trans_date": "2020-04-23",
            "trans_time": "10:23:15",
            "trans_combo": "1",
            "trans_text": "Bond Paper - Short",
            "radio": null,
            "checkbox": "",
            "trans_number": "250.000000",
            "trans_id": null,
            "trans_desc": null,
            "status": "Active",
            "updated_at": null,
            "created_at": null
        },
        {
            "id": "4",
            "trans_date": "2020-04-23",
            "trans_time": "14:04:48",
            "trans_combo": "1",
            "trans_text": "Bond Paper - Short",
            "radio": "6",
            "checkbox": "",
            "trans_number": "250.000000",
            "trans_id": null,
            "trans_desc": "sada",
            "status": "Active",
            "updated_at": null,
            "created_at": null
        },
        {
            "id": "5",
            "trans_date": "2020-04-23",
            "trans_time": "14:28:28",
            "trans_combo": "4",
            "trans_text": "Ballpen - Black",
            "radio": null,
            "checkbox": "",
            "trans_number": "15.000000",
            "trans_id": null,
            "trans_desc": "sadsa",
            "status": "Active",
            "updated_at": null,
            "created_at": null
        },
        {
            "id": "7",
            "trans_date": "2020-04-24",
            "trans_time": "10:33:06",
            "trans_combo": "1",
            "trans_text": "Bond Paper - Short",
            "radio": null,
            "checkbox": "",
            "trans_number": "250.000000",
            "trans_id": null,
            "trans_desc": null,
            "status": "Active",
            "updated_at": null,
            "created_at": null
        }
    ],
    "error": ""
}
```

### HTTP Request
`GET api/ranz/show`


<!-- END_997f9b041a1e4f283a96e811ff972e2b -->

<!-- START_a80c8ebc1bd66aa9d86775b0801d22a9 -->
## api/ranz/print
> Example request:

```bash
curl -X POST \
    "http://localhost/api/ranz/print" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/ranz/print"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/ranz/print`


<!-- END_a80c8ebc1bd66aa9d86775b0801d22a9 -->

<!-- START_496a71303b80d16ceaf34f9a01f5e409 -->
## api/ranz/edit/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/ranz/edit/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/ranz/edit/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": {
        "main": [
            {
                "id": "1",
                "trans_date": "2020-04-23",
                "trans_time": "10:23:15",
                "trans_combo": "1",
                "trans_text": "Bond Paper - Short",
                "radio": null,
                "checkbox": "",
                "trans_number": null,
                "trans_id": "0",
                "trans_desc": null,
                "status": "Active",
                "updated_at": "2020-04-23 02:23:48",
                "created_at": "2020-04-23 02:23:48"
            }
        ],
        "details": [],
        "chk": []
    },
    "error": ""
}
```

### HTTP Request
`GET api/ranz/edit/{id}`


<!-- END_496a71303b80d16ceaf34f9a01f5e409 -->

<!-- START_355e6474d59b3dfc54d4108626d037c3 -->
## api/Medical/displayData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Medical/displayData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Medical/displayData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Medical/displayData`


<!-- END_355e6474d59b3dfc54d4108626d037c3 -->

<!-- START_ed03ca6652fc077e50d6dae46fbea536 -->
## api/Medical/filterData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Medical/filterData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Medical/filterData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Medical/filterData`


<!-- END_ed03ca6652fc077e50d6dae46fbea536 -->

<!-- START_d73df2342bfa3e9971ade10bc1d6c7f7 -->
## api/Medical/printList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Medical/printList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Medical/printList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Medical/printList`


<!-- END_d73df2342bfa3e9971ade10bc1d6c7f7 -->

<!-- START_5f6cf280433648fe73ae32552c5d6c38 -->
## api/Medical/printDtl
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Medical/printDtl" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Medical/printDtl"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Medical/printDtl`


<!-- END_5f6cf280433648fe73ae32552c5d6c38 -->

<!-- START_5f8a59474033d0839ac2cbe47ad7bebe -->
## api/getDepartment
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getDepartment" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getDepartment"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getDepartment`


<!-- END_5f8a59474033d0839ac2cbe47ad7bebe -->

<!-- START_67f0395e8dc28f217ab196fd367fdd7c -->
## api/getSOF
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getSOF" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getSOF"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getSOF`


<!-- END_67f0395e8dc28f217ab196fd367fdd7c -->

<!-- START_55383f2a2f73372e1b00542632b9f9c1 -->
## api/getRoutingSetup
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getRoutingSetup" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getRoutingSetup"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getRoutingSetup`


<!-- END_55383f2a2f73372e1b00542632b9f9c1 -->

<!-- START_f79bac2b4c108039cd0e9c10c36a7100 -->
## api/getAllDepatmentEmployee
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getAllDepatmentEmployee" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getAllDepatmentEmployee"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getAllDepatmentEmployee`


<!-- END_f79bac2b4c108039cd0e9c10c36a7100 -->

<!-- START_c4ec728e6b7f2a0e0c7f3381353ee562 -->
## api/globalVariable
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/globalVariable" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/globalVariable"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/globalVariable`


<!-- END_c4ec728e6b7f2a0e0c7f3381353ee562 -->

<!-- START_c242650d2d751e442e1e015292af1ac5 -->
## api/getItem
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getItem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getItem"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getItem`


<!-- END_c242650d2d751e442e1e015292af1ac5 -->

<!-- START_a9a967240e342441976318e96ce069c4 -->
## api/getBusinessForAssessment
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getBusinessForAssessment" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getBusinessForAssessment"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getBusinessForAssessment`


<!-- END_a9a967240e342441976318e96ce069c4 -->

<!-- START_ef037da9542b6ba76bea47cfd9d17675 -->
## api/getPersonProfileList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getPersonProfileList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getPersonProfileList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getPersonProfileList`


<!-- END_ef037da9542b6ba76bea47cfd9d17675 -->

<!-- START_3705d6a245da5ba680349393af4c80eb -->
## api/profileUpdate
> Example request:

```bash
curl -X POST \
    "http://localhost/api/profileUpdate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/profileUpdate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/profileUpdate`


<!-- END_3705d6a245da5ba680349393af4c80eb -->

<!-- START_7ce7618a0446058cacfd4267182c5bae -->
## api/getProfile/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/getProfile/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/getProfile/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/getProfile/{id}`


<!-- END_7ce7618a0446058cacfd4267182c5bae -->

<!-- START_c5fcd2b1753d1e5f43dd4d409b3416ab -->
## api/porfileUpload
> Example request:

```bash
curl -X POST \
    "http://localhost/api/porfileUpload" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/porfileUpload"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/porfileUpload`


<!-- END_c5fcd2b1753d1e5f43dd4d409b3416ab -->

<!-- START_5a5c46642b4b45f3d7c8191eb62c2fa6 -->
## api/displaybillingfees
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/displaybillingfees" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/displaybillingfees"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/displaybillingfees`


<!-- END_5a5c46642b4b45f3d7c8191eb62c2fa6 -->

<!-- START_be372649f159175652dcb9f42428747a -->
## api/Address/province
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Address/province" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Address/province"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Address/province`


<!-- END_be372649f159175652dcb9f42428747a -->

<!-- START_b9627aecafe17f43c3e1565fd6266814 -->
## api/Address/city/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Address/city/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Address/city/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Address/city/{id}`


<!-- END_b9627aecafe17f43c3e1565fd6266814 -->

<!-- START_6b38b03a4d2d81dfa8e09138c5eefa0f -->
## api/Address/barangay/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Address/barangay/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Address/barangay/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Address/barangay/{id}`


<!-- END_6b38b03a4d2d81dfa8e09138c5eefa0f -->

<!-- START_19ff1b6f8ce19d3c444e9b518e8f7160 -->
## api/auth/logout
> Example request:

```bash
curl -X POST \
    "http://localhost/api/auth/logout" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/auth/logout"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/auth/logout`


<!-- END_19ff1b6f8ce19d3c444e9b518e8f7160 -->

<!-- START_fc1e4f6a697e3c48257de845299b71d5 -->
## Display a listing of the user resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/users" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/users`


<!-- END_fc1e4f6a697e3c48257de845299b71d5 -->

<!-- START_12e37982cc5398c7100e59625ebb5514 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST \
    "http://localhost/api/users" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/users`


<!-- END_12e37982cc5398c7100e59625ebb5514 -->

<!-- START_8653614346cb0e3d444d164579a0a0a2 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/users/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/users/{user}`


<!-- END_8653614346cb0e3d444d164579a0a0a2 -->

<!-- START_48a3115be98493a3c866eb0e23347262 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/users/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/users/{user}`

`PATCH api/users/{user}`


<!-- END_48a3115be98493a3c866eb0e23347262 -->

<!-- START_d2db7a9fe3abd141d5adbc367a88e969 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/users/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/users/{user}`


<!-- END_d2db7a9fe3abd141d5adbc367a88e969 -->

<!-- START_694ee91baac957b25de9be13e5220a30 -->
## Get permissions from role

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/users/1/permissions" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users/1/permissions"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/users/{user}/permissions`


<!-- END_694ee91baac957b25de9be13e5220a30 -->

<!-- START_a9e654117a147d3e9cd801b7d8a501d8 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/users/1/permissions" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/users/1/permissions"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/users/{user}/permissions`


<!-- END_a9e654117a147d3e9cd801b7d8a501d8 -->

<!-- START_6470e6b987921f5c45bf7a2d8e674f57 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/roles" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/roles"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/roles`


<!-- END_6470e6b987921f5c45bf7a2d8e674f57 -->

<!-- START_90c780acaefab9740431579512d07101 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST \
    "http://localhost/api/roles" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/roles"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/roles`


<!-- END_90c780acaefab9740431579512d07101 -->

<!-- START_eb37fe1fa9305b4b78850dd87031670b -->
## Display the specified resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/roles/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/roles/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/roles/{role}`


<!-- END_eb37fe1fa9305b4b78850dd87031670b -->

<!-- START_cccebfff0074c9c5f499e215eee84e86 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/roles/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/roles/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/roles/{role}`

`PATCH api/roles/{role}`


<!-- END_cccebfff0074c9c5f499e215eee84e86 -->

<!-- START_9aab750214722ffceebef64f24a2e175 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/roles/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/roles/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/roles/{role}`


<!-- END_9aab750214722ffceebef64f24a2e175 -->

<!-- START_8c1fe150e00d87ed3557102418113f26 -->
## Get permissions from role

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/roles/1/permissions" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/roles/1/permissions"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/roles/{role}/permissions`


<!-- END_8c1fe150e00d87ed3557102418113f26 -->

<!-- START_42db014707f615cd5cafb5ad1b6d0675 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/permissions" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/permissions"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/permissions`


<!-- END_42db014707f615cd5cafb5ad1b6d0675 -->

<!-- START_d513e82f79d47649a14d2e59fda93073 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST \
    "http://localhost/api/permissions" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/permissions"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/permissions`


<!-- END_d513e82f79d47649a14d2e59fda93073 -->

<!-- START_29ec1a9c6f20445dcd75bf6a4cc63e2a -->
## Display the specified resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/permissions/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/permissions/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "User is not logged in."
}
```

### HTTP Request
`GET api/permissions/{permission}`


<!-- END_29ec1a9c6f20445dcd75bf6a4cc63e2a -->

<!-- START_cbdd1fce06181b5d5d8d0f3ae85ed0ee -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/permissions/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/permissions/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/permissions/{permission}`

`PATCH api/permissions/{permission}`


<!-- END_cbdd1fce06181b5d5d8d0f3ae85ed0ee -->

<!-- START_58309983000c47ce901812498144165a -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/permissions/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/permissions/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/permissions/{permission}`


<!-- END_58309983000c47ce901812498144165a -->

<!-- START_e2b847f0d370a8e62c8a6ddf3201cc98 -->
## api/Setup/displayData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/displayData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/displayData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [
        {
            "id": "1",
            "transDate": "2020-05-08",
            "groupType": "Group 1",
            "description": "Sample 10",
            "remarks": "test",
            "transStat": "Cancelled",
            "created_at": "2020-05-13 12:52:50",
            "updated_at": "0000-00-00 00:00:00"
        },
        {
            "id": "2",
            "transDate": "2020-05-08",
            "groupType": "Group 1",
            "description": "Sample 11",
            "remarks": "new remarks",
            "transStat": "Active",
            "created_at": "2020-05-11 14:38:18",
            "updated_at": "0000-00-00 00:00:00"
        },
        {
            "id": "3",
            "transDate": "2020-05-08",
            "groupType": "Group 2",
            "description": "Sample 20",
            "remarks": "",
            "transStat": "Active",
            "created_at": "2020-05-08 10:05:29",
            "updated_at": "0000-00-00 00:00:00"
        },
        {
            "id": "4",
            "transDate": "2020-05-08",
            "groupType": "Group 2",
            "description": "Sample 21",
            "remarks": "",
            "transStat": "Active",
            "created_at": "2020-05-08 10:05:30",
            "updated_at": "0000-00-00 00:00:00"
        },
        {
            "id": "5",
            "transDate": "2020-05-10",
            "groupType": "Group 2",
            "description": "test1",
            "remarks": "remarks",
            "transStat": "Active",
            "created_at": "2020-05-13 15:31:19",
            "updated_at": "0000-00-00 00:00:00"
        },
        {
            "id": "6",
            "transDate": "2020-05-10",
            "groupType": "Group 2",
            "description": "test2",
            "remarks": "remarks",
            "transStat": "Active",
            "created_at": "2020-05-11 14:35:08",
            "updated_at": "0000-00-00 00:00:00"
        }
    ],
    "error": ""
}
```

### HTTP Request
`GET api/Setup/displayData`


<!-- END_e2b847f0d370a8e62c8a6ddf3201cc98 -->

<!-- START_d277c44f6082ea03ce50e3a59bd7dc9f -->
## api/Setup/filterData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/filterData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/filterData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [],
    "error": ""
}
```

### HTTP Request
`GET api/Setup/filterData`


<!-- END_d277c44f6082ea03ce50e3a59bd7dc9f -->

<!-- START_046ce5432ba19f1cf6a4dcc35d766f55 -->
## api/Setup/print
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/print" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/print"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/print`


<!-- END_046ce5432ba19f1cf6a4dcc35d766f55 -->

<!-- START_a6db6a4bd0a79f2fc518d5c50a77d70d -->
## api/Setup/printMain
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/printMain" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/printMain"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/printMain`


<!-- END_a6db6a4bd0a79f2fc518d5c50a77d70d -->

<!-- START_4a928246427c16f932fd2613189c9785 -->
## api/Setup/printDtl
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/printDtl" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/printDtl"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/printDtl`


<!-- END_4a928246427c16f932fd2613189c9785 -->

<!-- START_379807b6040e84ebfef9c36383d2c6cd -->
## api/Setup/groupData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/groupData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/groupData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [
        {
            "groupType": "Group 1",
            "transStat": "Active"
        },
        {
            "groupType": "Group 2",
            "transStat": "Active"
        }
    ],
    "error": ""
}
```

### HTTP Request
`GET api/Setup/groupData`


<!-- END_379807b6040e84ebfef9c36383d2c6cd -->

<!-- START_03aea03ce02c7ebef2f0882cc1e31a1c -->
## api/Setup/customData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/customData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/customData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [],
    "error": ""
}
```

### HTTP Request
`GET api/Setup/customData`


<!-- END_03aea03ce02c7ebef2f0882cc1e31a1c -->

<!-- START_761a382659eff2ce94110a5b1e5cebfc -->
## api/Setup/save
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/save" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/save"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/save`


<!-- END_761a382659eff2ce94110a5b1e5cebfc -->

<!-- START_219c7a38673e508df37d07c496adc857 -->
## api/Setup/maxNum
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/maxNum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/maxNum"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [
        {
            "id": "1",
            "maxID": "FRM-007-2020"
        }
    ],
    "error": ""
}
```

### HTTP Request
`GET api/Setup/maxNum`


<!-- END_219c7a38673e508df37d07c496adc857 -->

<!-- START_652f02b1457a592addb307c5caba226e -->
## api/Setup/editData/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/editData/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/editData/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": {
        "main": [
            {
                "id": "1",
                "transDate": "2020-05-08",
                "groupType": "Group 1",
                "description": "Sample 10",
                "remarks": "test",
                "transStat": "Cancelled",
                "created_at": "2020-05-13 12:52:50",
                "updated_at": "0000-00-00 00:00:00"
            }
        ],
        "detail": [
            {
                "id": "1",
                "transDate": "2020-05-08",
                "groupType": "Group 1",
                "description": "Sample 10",
                "remarks": "test"
            }
        ]
    },
    "error": ""
}
```

### HTTP Request
`GET api/Setup/editData/{id}`


<!-- END_652f02b1457a592addb307c5caba226e -->

<!-- START_133445d78ad5a98ea1682bb0b31f56ed -->
## api/Setup/update
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/update" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/update"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/update`


<!-- END_133445d78ad5a98ea1682bb0b31f56ed -->

<!-- START_69de2d77e23eeaa0ffbf55b0bd4b512b -->
## api/Setup/cancel/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/cancel/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/cancel/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": {
        "Message": "Transaction completed successfully.",
        "status": "success"
    },
    "error": ""
}
```

### HTTP Request
`GET api/Setup/cancel/{id}`


<!-- END_69de2d77e23eeaa0ffbf55b0bd4b512b -->

<!-- START_cdcc9a556177488d0caa39982502b391 -->
## api/Setup/viewData/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Setup/viewData/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/viewData/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [
        {
            "id": "1",
            "transDate": "2020-05-08",
            "groupType": "Group 1",
            "description": "Sample 10",
            "remarks": "test",
            "transStat": "Cancelled",
            "created_at": "2020-05-13 12:52:50",
            "updated_at": "0000-00-00 00:00:00"
        }
    ],
    "error": ""
}
```

### HTTP Request
`GET api/Setup/viewData/{id}`


<!-- END_cdcc9a556177488d0caa39982502b391 -->

<!-- START_6560309725976ef27d813356ed7e1def -->
## api/Setup/modify
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/modify" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/modify"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/modify`


<!-- END_6560309725976ef27d813356ed7e1def -->

<!-- START_8ac2a7cd0a0b4ce21bfcbc152dcacaa9 -->
## api/Setup/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Setup/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Setup/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Setup/store`


<!-- END_8ac2a7cd0a0b4ce21bfcbc152dcacaa9 -->

<!-- START_d0426c12cb7c3ec8132db3c3aa665c89 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Environmental/displayData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/displayData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Environmental/displayData`


<!-- END_d0426c12cb7c3ec8132db3c3aa665c89 -->

<!-- START_39d09171a9d421c482a24f594e0f5f5d -->
## api/Environmental/filterData
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Environmental/filterData" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/filterData"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Environmental/filterData`


<!-- END_39d09171a9d421c482a24f594e0f5f5d -->

<!-- START_d110dcfc315df73b52ad4ab62843cbd5 -->
## api/Environmental/printMain
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Environmental/printMain" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/printMain"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Environmental/printMain`


<!-- END_d110dcfc315df73b52ad4ab62843cbd5 -->

<!-- START_506c2f70a59bbb8172b1e7e4702d96c1 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Environmental/businessList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/businessList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Environmental/businessList`


<!-- END_506c2f70a59bbb8172b1e7e4702d96c1 -->

<!-- START_a3f9fa925929dd4b537dd831f871b602 -->
## api/Environmental/save
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Environmental/save" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/save"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Environmental/save`


<!-- END_a3f9fa925929dd4b537dd831f871b602 -->

<!-- START_697ab1c9a8a76dcc0469ecdb08ee6705 -->
## api/Environmental/editData/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Environmental/editData/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/editData/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Environmental/editData/{id}`


<!-- END_697ab1c9a8a76dcc0469ecdb08ee6705 -->

<!-- START_af5867192ec55e7c7c691aff2fbff6fb -->
## api/Environmental/update
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Environmental/update" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/update"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Environmental/update`


<!-- END_af5867192ec55e7c7c691aff2fbff6fb -->

<!-- START_336f8385a9664b7a7a30ff398a430e37 -->
## api/Environmental/enviCertPrint/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Environmental/enviCertPrint/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/enviCertPrint/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Environmental/enviCertPrint/{id}`


<!-- END_336f8385a9664b7a7a30ff398a430e37 -->

<!-- START_33ada45ce77e7950edab776947f4bd82 -->
## api/Environmental/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Environmental/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Environmental/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Environmental/store`


<!-- END_33ada45ce77e7950edab776947f4bd82 -->

<!-- START_74f3df3296c08964716794967f61efcc -->
## api/HealthCard/getHealthCardList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/HealthCard/getHealthCardList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/HealthCard/getHealthCardList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/HealthCard/getHealthCardList`


<!-- END_74f3df3296c08964716794967f61efcc -->

<!-- START_bd5a8f14807b3d3d6497f6576a87263c -->
## api/HealthCard/printHealthCardList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/HealthCard/printHealthCardList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/HealthCard/printHealthCardList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/HealthCard/printHealthCardList`


<!-- END_bd5a8f14807b3d3d6497f6576a87263c -->

<!-- START_f7fd9eef785c8cdf058965864ebad66d -->
## api/Sanitary/show/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/show/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/show/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/show/{id}`


<!-- END_f7fd9eef785c8cdf058965864ebad66d -->

<!-- START_fac5d73614d78cb8330259d7965b7f35 -->
## api/Sanitary/edit/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/edit/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/edit/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/edit/{id}`


<!-- END_fac5d73614d78cb8330259d7965b7f35 -->

<!-- START_776ad5842c7c6f589c999d4ba8d53066 -->
## api/Sanitary/store
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/store`


<!-- END_776ad5842c7c6f589c999d4ba8d53066 -->

<!-- START_97a733a8d067654f9a26af6e160f4b6f -->
## api/Sanitary/save
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/save" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/save"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/save`


<!-- END_97a733a8d067654f9a26af6e160f4b6f -->

<!-- START_6a2caf9e0e7319e15c178260974f8d0e -->
## api/Sanitary/insertReason
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/insertReason" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/insertReason"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/insertReason`


<!-- END_6a2caf9e0e7319e15c178260974f8d0e -->

<!-- START_20904211508b1c6afdb864c32b9cd0a9 -->
## api/Sanitary/getCategory
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/getCategory" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/getCategory"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/getCategory`


<!-- END_20904211508b1c6afdb864c32b9cd0a9 -->

<!-- START_c663efe532d664cd0a5cd1104cd3aee4 -->
## api/Sanitary/sanitaryList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Sanitary/sanitaryList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/sanitaryList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Sanitary/sanitaryList`


<!-- END_c663efe532d664cd0a5cd1104cd3aee4 -->

<!-- START_783a1b87b066366dc156dc21380e5a80 -->
## api/Sanitary/printSanitaryList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Sanitary/printSanitaryList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/printSanitaryList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Sanitary/printSanitaryList`


<!-- END_783a1b87b066366dc156dc21380e5a80 -->

<!-- START_2a0456b151509d959e07e1de27d4456d -->
## api/Sanitary/printSanitaryCertificate
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Sanitary/printSanitaryCertificate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Sanitary/printSanitaryCertificate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Sanitary/printSanitaryCertificate`


<!-- END_2a0456b151509d959e07e1de27d4456d -->

<!-- START_2fe8f9f67b46b71712571ec36e221a47 -->
## api/Calendar/display
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Calendar/display" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Calendar/display"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Calendar/display`


<!-- END_2fe8f9f67b46b71712571ec36e221a47 -->

<!-- START_66ea588ea40e17b4055de856789b3145 -->
## api/Calendar/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Calendar/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Calendar/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Calendar/store`


<!-- END_66ea588ea40e17b4055de856789b3145 -->

<!-- START_d6332ef3401b6ae3e2cbdde2ef6c0bd0 -->
## api/User/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/User/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/User/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/User/store`


<!-- END_d6332ef3401b6ae3e2cbdde2ef6c0bd0 -->

<!-- START_d2cec79b3ff6a002b894465993c204f7 -->
## api/User/show
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/User/show" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"aliquam","body":"ut","type":"repudiandae","author_id":2,"thumbnail":"ut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/User/show"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "aliquam",
    "body": "ut",
    "type": "repudiandae",
    "author_id": 2,
    "thumbnail": "ut"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 57,
                "name": "Richard Porras",
                "email": "itdqporras@gmail.com",
                "email_verified_at": "2020-05-20T01:45:03.000000Z",
                "created_at": "2020-05-20T01:18:26.000000Z",
                "updated_at": "2020-05-20T01:45:03.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": "57\/1589940842.png"
            },
            {
                "id": 58,
                "name": "Gigil Torres",
                "email": "jay02tor@gmail.com",
                "email_verified_at": "2020-05-20T01:45:03.000000Z",
                "created_at": "2020-05-21T00:14:52.000000Z",
                "updated_at": "2020-05-21T00:14:52.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": null
            },
            {
                "id": 62,
                "name": "Acka Peachie",
                "email": "ackapeachiebarro1986@gmail.com",
                "email_verified_at": null,
                "created_at": "2020-05-22T04:49:20.000000Z",
                "updated_at": "2020-05-22T04:49:20.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": null
            },
            {
                "id": 60,
                "name": "Sly Grey",
                "email": "watdaif@yahoo.com",
                "email_verified_at": "2020-05-21T12:21:38.000000Z",
                "created_at": "2020-05-21T00:27:06.000000Z",
                "updated_at": "2020-05-21T00:27:06.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": null
            },
            {
                "id": 61,
                "name": "Joe",
                "email": "resalinajoeann@gmail.com",
                "email_verified_at": "2020-05-21T02:01:57.000000Z",
                "created_at": "2020-05-21T02:00:25.000000Z",
                "updated_at": "2020-05-21T02:01:57.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": "61\/1590027292.png"
            },
            {
                "id": 63,
                "name": "Kenneth P. Harina",
                "email": "kenneth.harina@yahoo.com",
                "email_verified_at": "2020-05-22T11:53:32.000000Z",
                "created_at": "2020-05-22T05:15:56.000000Z",
                "updated_at": "2020-05-22T11:53:32.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": null
            },
            {
                "id": 64,
                "name": "Nhinlonzo",
                "email": "ninoalonzo19@gmail.com",
                "email_verified_at": "2020-05-27T12:30:08.000000Z",
                "created_at": "2020-05-27T00:29:35.000000Z",
                "updated_at": "2020-05-27T00:30:08.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": "64\/1590539523.png"
            },
            {
                "id": 65,
                "name": "Mark jayson Ubas",
                "email": "markkier24@gmail.com",
                "email_verified_at": "2020-05-27T12:40:20.000000Z",
                "created_at": "2020-05-27T00:39:16.000000Z",
                "updated_at": "2020-05-27T00:40:20.000000Z",
                "verified": "0",
                "Employee_id": "0",
                "status": "0",
                "isActive": "Active",
                "image_path": null
            }
        ]
    },
    "error": ""
}
```

### HTTP Request
`GET api/User/show`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | The title of the post.
        `body` | string |  required  | The content of the post.
        `type` | string |  optional  | The type of post to create. Defaults to 'textophonious'.
        `author_id` | integer |  optional  | the ID of the author.
        `thumbnail` | image |  optional  | This is required if the post type is 'imagelicious'.
    
<!-- END_d2cec79b3ff6a002b894465993c204f7 -->

<!-- START_b19e9071640d9f6805da0363167ac3ec -->
## api/User/update
> Example request:

```bash
curl -X POST \
    "http://localhost/api/User/update" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/User/update"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/User/update`


<!-- END_b19e9071640d9f6805da0363167ac3ec -->

<!-- START_8e2a7827e037ac05d7392f055851b683 -->
## api/User/cancel/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/User/cancel/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/User/cancel/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": {
        "Message": "Transaction completed successfully.",
        "status": "success"
    },
    "error": ""
}
```

### HTTP Request
`GET api/User/cancel/{id}`


<!-- END_8e2a7827e037ac05d7392f055851b683 -->

<!-- START_8dd59b86ecf657a55afa6b182b483632 -->
## api/User/display
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/User/display" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/User/display"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "success": true,
    "data": [
        {
            "name": "Admin",
            "email": "admin@laravue.dev",
            "isActive": "Active"
        },
        {
            "name": "Richard Porras",
            "email": "itdqporras@gmail.com",
            "isActive": "Active"
        },
        {
            "name": "Gigil Torres",
            "email": "jay02tor@gmail.com",
            "isActive": "Active"
        },
        {
            "name": "Acka Peachie",
            "email": "ackapeachiebarro1986@gmail.com",
            "isActive": "Active"
        },
        {
            "name": "Sly Grey",
            "email": "watdaif@yahoo.com",
            "isActive": "Active"
        },
        {
            "name": "Joe",
            "email": "resalinajoeann@gmail.com",
            "isActive": "Active"
        },
        {
            "name": "Kenneth P. Harina",
            "email": "kenneth.harina@yahoo.com",
            "isActive": "Active"
        },
        {
            "name": "Nhinlonzo",
            "email": "ninoalonzo19@gmail.com",
            "isActive": "Active"
        },
        {
            "name": "Mark jayson Ubas",
            "email": "markkier24@gmail.com",
            "isActive": "Active"
        }
    ],
    "error": ""
}
```

### HTTP Request
`GET api/User/display`


<!-- END_8dd59b86ecf657a55afa6b182b483632 -->

<!-- START_775c10844b5cb1d8de98b57c71338055 -->
## api/Employee/list
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Employee/list" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Employee/list"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Employee/list`


<!-- END_775c10844b5cb1d8de98b57c71338055 -->

<!-- START_276162bff909bcf3ad8a88b288bbd13c -->
## api/Employee/getEmployeeList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Employee/getEmployeeList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Employee/getEmployeeList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Employee/getEmployeeList`


<!-- END_276162bff909bcf3ad8a88b288bbd13c -->

<!-- START_1d9de55647cc1a98b089a7f755b2616c -->
## api/Employee/getEmployeeListTable
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Employee/getEmployeeListTable" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Employee/getEmployeeListTable"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Employee/getEmployeeListTable`


<!-- END_1d9de55647cc1a98b089a7f755b2616c -->

<!-- START_98337222eb0e1b0eb8d7d7b7b77d64ef -->
## api/Group
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Group" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Group"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Group`


<!-- END_98337222eb0e1b0eb8d7d7b7b77d64ef -->

<!-- START_c0b15e6bf539871d4ba90b64661f2a35 -->
## api/Group/show
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Group/show" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Group/show"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Group/show`


<!-- END_c0b15e6bf539871d4ba90b64661f2a35 -->

<!-- START_4607cb56fbfa0480ddbfae8bf5aff41a -->
## api/Group/edit/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Group/edit/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Group/edit/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Group/edit/{id}`


<!-- END_4607cb56fbfa0480ddbfae8bf5aff41a -->

<!-- START_a1c235e1576095252387cb3a05baef94 -->
## api/Group/cancel/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Group/cancel/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Group/cancel/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Group/cancel/{id}`


<!-- END_a1c235e1576095252387cb3a05baef94 -->

<!-- START_81300496026dd615f19e5785632da7b8 -->
## api/Group/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Group/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Group/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Group/store`


<!-- END_81300496026dd615f19e5785632da7b8 -->

<!-- START_4b3d9fb14136d96d15b58c8b05846668 -->
## api/Group/Display_Name
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Group/Display_Name" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Group/Display_Name"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Group/Display_Name`


<!-- END_4b3d9fb14136d96d15b58c8b05846668 -->

<!-- START_62924b10a01d04c4659c56a68903f63d -->
## api/Doctracker
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker`


<!-- END_62924b10a01d04c4659c56a68903f63d -->

<!-- START_8504342c794e677364ee65683565f376 -->
## api/Doctracker/show
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/show" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/show"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/show`


<!-- END_8504342c794e677364ee65683565f376 -->

<!-- START_bdc38f8a8f202be0594cc5a86db05e44 -->
## api/Doctracker/getRef
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/getRef" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/getRef"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/getRef`


<!-- END_bdc38f8a8f202be0594cc5a86db05e44 -->

<!-- START_67d30a43ce1878b677296935ee04bf66 -->
## api/Doctracker/getFlowChart
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/getFlowChart" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/getFlowChart"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/getFlowChart`


<!-- END_67d30a43ce1878b677296935ee04bf66 -->

<!-- START_33faa98c410c93d73a440dbaab5af2e5 -->
## api/Doctracker/edit/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/edit/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/edit/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/edit/{id}`


<!-- END_33faa98c410c93d73a440dbaab5af2e5 -->

<!-- START_7098c6af5e8fa4cfa56d38da37182326 -->
## api/Doctracker/list
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/list" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/list"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/list`


<!-- END_7098c6af5e8fa4cfa56d38da37182326 -->

<!-- START_c9901abfc7abd64195609c980a4bfae4 -->
## api/Doctracker/store
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Doctracker/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Doctracker/store`


<!-- END_c9901abfc7abd64195609c980a4bfae4 -->

<!-- START_f17031bab015a22d34885248ab973151 -->
## api/Doctracker/flowName
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/flowName" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/flowName"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/flowName`


<!-- END_f17031bab015a22d34885248ab973151 -->

<!-- START_2137cee93a04811d9d1bc0274807e9f9 -->
## api/Doctracker/docType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/docType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/docType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/docType`


<!-- END_2137cee93a04811d9d1bc0274807e9f9 -->

<!-- START_a99131e67c891084cceefc418f7c58b3 -->
## api/Doctracker/showPerType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/showPerType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/showPerType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/showPerType`


<!-- END_a99131e67c891084cceefc418f7c58b3 -->

<!-- START_7a7c2cbc1b1a9b3bbb714f420ec34e59 -->
## api/Doctracker/showStatus/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/showStatus/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/showStatus/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/showStatus/{id}`


<!-- END_7a7c2cbc1b1a9b3bbb714f420ec34e59 -->

<!-- START_ad6a8e8340c6d3882acb020c69d3e694 -->
## api/Doctracker/cancel/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Doctracker/cancel/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/cancel/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Doctracker/cancel/{id}`


<!-- END_ad6a8e8340c6d3882acb020c69d3e694 -->

<!-- START_7216d123a8fe96835e3d33c9c879e9cd -->
## api/Doctracker/upload
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Doctracker/upload" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Doctracker/upload"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Doctracker/upload`


<!-- END_7216d123a8fe96835e3d33c9c879e9cd -->

<!-- START_b8f418c7b4b60dab13b57ff185dda475 -->
## api/General/Business/getBusinessStatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/General/Business/getBusinessStatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/General/Business/getBusinessStatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/General/Business/getBusinessStatus`


<!-- END_b8f418c7b4b60dab13b57ff185dda475 -->

<!-- START_eed59cdfbd00e233555de646e878fe50 -->
## api/General/Business/getBusinessType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/General/Business/getBusinessType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/General/Business/getBusinessType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/General/Business/getBusinessType`


<!-- END_eed59cdfbd00e233555de646e878fe50 -->

<!-- START_9a5df8da99b0a62d028ec78e6d8211a1 -->
## api/General/Business/getBusinessKind
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/General/Business/getBusinessKind" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/General/Business/getBusinessKind"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/General/Business/getBusinessKind`


<!-- END_9a5df8da99b0a62d028ec78e6d8211a1 -->

<!-- START_944074bca14cd84dc4cf86626cb805e0 -->
## api/General/Business/getofficeType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/General/Business/getofficeType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/General/Business/getofficeType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/General/Business/getofficeType`


<!-- END_944074bca14cd84dc4cf86626cb805e0 -->

<!-- START_e06f271ae9e3412f1354d8d6994560f3 -->
## api/General/Business/getBSPType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/General/Business/getBSPType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/General/Business/getBSPType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/General/Business/getBSPType`


<!-- END_e06f271ae9e3412f1354d8d6994560f3 -->

<!-- START_1e73949ae3ff930131afae533c05e7f1 -->
## api/LGUMain/business/businessTaxLedger
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessTaxLedger" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessTaxLedger"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessTaxLedger`


<!-- END_1e73949ae3ff930131afae533c05e7f1 -->

<!-- START_a7f70b44b8fbd667058f530669bae9fc -->
## api/LGUMain/business/businessTaxDeliquency
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessTaxDeliquency" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessTaxDeliquency"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessTaxDeliquency`


<!-- END_a7f70b44b8fbd667058f530669bae9fc -->

<!-- START_1f8f18ea83c2cbe1135994ad698ad4a4 -->
## api/LGUMain/business/getAZCol
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getAZCol" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getAZCol"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getAZCol`


<!-- END_1f8f18ea83c2cbe1135994ad698ad4a4 -->

<!-- START_c428e3e979867371bf8b167cb6aa7d48 -->
## api/LGUMain/business/getQuarter
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getQuarter" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getQuarter"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getQuarter`


<!-- END_c428e3e979867371bf8b167cb6aa7d48 -->

<!-- START_dfed59669099ed5d51734e759cf0ec9e -->
## api/LGUMain/business/getStreet
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getStreet" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getStreet"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getStreet`


<!-- END_dfed59669099ed5d51734e759cf0ec9e -->

<!-- START_305c48fd69df56a1192dc737d6186a27 -->
## api/LGUMain/business/getClassification
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getClassification" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getClassification"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getClassification`


<!-- END_305c48fd69df56a1192dc737d6186a27 -->

<!-- START_07b6c29e4b224a98b7abc9f54cae78be -->
## api/LGUMain/business/getBustype
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getBustype" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getBustype"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getBustype`


<!-- END_07b6c29e4b224a98b7abc9f54cae78be -->

<!-- START_8ad2fb0f53e3fc5c589a4127f30eac2e -->
## api/LGUMain/business/getBusstatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getBusstatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getBusstatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getBusstatus`


<!-- END_8ad2fb0f53e3fc5c589a4127f30eac2e -->

<!-- START_ae6866cf6008d15edbc97df1329ecda6 -->
## api/LGUMain/business/businessTaxSubsidiaryLedgerPrint/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessTaxSubsidiaryLedgerPrint/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessTaxSubsidiaryLedgerPrint/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessTaxSubsidiaryLedgerPrint/{id}`


<!-- END_ae6866cf6008d15edbc97df1329ecda6 -->

<!-- START_de35154623c6254202608c27c3ccaa74 -->
## api/LGUMain/business/printPhistory/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/printPhistory/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printPhistory/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/printPhistory/{id}`


<!-- END_de35154623c6254202608c27c3ccaa74 -->

<!-- START_f2805b29d15d2926872aaf556a3089f3 -->
## api/LGUMain/business/printmain
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printmain" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printmain"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printmain`


<!-- END_f2805b29d15d2926872aaf556a3089f3 -->

<!-- START_f3f0ce5032ba059f7a14c11cd63932fc -->
## api/LGUMain/business/printNot
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printNot" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printNot"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printNot`


<!-- END_f3f0ce5032ba059f7a14c11cd63932fc -->

<!-- START_7519a9b8872134646bede78c074a9875 -->
## api/LGUMain/business/printAllNot
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printAllNot" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printAllNot"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printAllNot`


<!-- END_7519a9b8872134646bede78c074a9875 -->

<!-- START_b2686681eb79107ee6e1e5a6fafe49dd -->
## api/LGUMain/business/printMaster
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printMaster" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printMaster"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printMaster`


<!-- END_b2686681eb79107ee6e1e5a6fafe49dd -->

<!-- START_da5d5127a902d74839848393b4be929d -->
## api/LGUMain/business/printBarangay
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printBarangay" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printBarangay"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printBarangay`


<!-- END_da5d5127a902d74839848393b4be929d -->

<!-- START_cae806478b954312f9e550d0aab4ec25 -->
## api/LGUMain/business/printTaxCertificate
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printTaxCertificate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printTaxCertificate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printTaxCertificate`


<!-- END_cae806478b954312f9e550d0aab4ec25 -->

<!-- START_0f36bddc3be5ab6055d7bafdcdc0492f -->
## api/LGUMain/business/businessTaxMaster
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessTaxMaster" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessTaxMaster"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessTaxMaster`


<!-- END_0f36bddc3be5ab6055d7bafdcdc0492f -->

<!-- START_f28af68d5f41c7ee5a35b98fc063a5ee -->
## api/LGUMain/business/getBusinessStatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getBusinessStatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getBusinessStatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getBusinessStatus`


<!-- END_f28af68d5f41c7ee5a35b98fc063a5ee -->

<!-- START_5b4e00174499e258958e29321d512c10 -->
## api/LGUMain/business/printBusinessList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printBusinessList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printBusinessList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printBusinessList`


<!-- END_5b4e00174499e258958e29321d512c10 -->

<!-- START_33c4faf7e9708a4d1d030c2c43afefe7 -->
## api/LGUMain/business/printClosure
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printClosure" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printClosure"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printClosure`


<!-- END_33c4faf7e9708a4d1d030c2c43afefe7 -->

<!-- START_96886649ebdd57b732984f30297de31d -->
## api/LGUMain/business/businessTaxLedger_history/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessTaxLedger_history/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessTaxLedger_history/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessTaxLedger_history/{id}`


<!-- END_96886649ebdd57b732984f30297de31d -->

<!-- START_a6731331cde1e57b35fcd1cddbd67095 -->
## api/LGUMain/business/businessPaymentStatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessPaymentStatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessPaymentStatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessPaymentStatus`


<!-- END_a6731331cde1e57b35fcd1cddbd67095 -->

<!-- START_492aeed1a3c79420de4d4a7297dc0d59 -->
## api/LGUMain/business/businessPermitStatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessPermitStatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessPermitStatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessPermitStatus`


<!-- END_492aeed1a3c79420de4d4a7297dc0d59 -->

<!-- START_d2005f667dfb540997b40f061dce3171 -->
## api/LGUMain/business/businessPermitPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessPermitPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessPermitPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessPermitPrint`


<!-- END_d2005f667dfb540997b40f061dce3171 -->

<!-- START_56e721fee63b26fe4c108894900fb6e6 -->
## api/LGUMain/business/businessPaymentPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessPaymentPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessPaymentPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessPaymentPrint`


<!-- END_56e721fee63b26fe4c108894900fb6e6 -->

<!-- START_ded96e7770f77082aaf2dd4ccd477104 -->
## api/LGUMain/business/businessEnterprise
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessEnterprise" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessEnterprise"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessEnterprise`


<!-- END_ded96e7770f77082aaf2dd4ccd477104 -->

<!-- START_d3c2cf948e601d405d563f3a5f6ecb3b -->
## api/LGUMain/business/businessEnterprisePrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessEnterprisePrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessEnterprisePrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessEnterprisePrint`


<!-- END_d3c2cf948e601d405d563f3a5f6ecb3b -->

<!-- START_4be52daad00547dfa5aa7855749158d1 -->
## api/LGUMain/business/businessBMBE
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessBMBE" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessBMBE"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessBMBE`


<!-- END_4be52daad00547dfa5aa7855749158d1 -->

<!-- START_7c133a74c1313708618e8060af0ffb9a -->
## api/LGUMain/business/businessBMBEPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessBMBEPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessBMBEPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessBMBEPrint`


<!-- END_7c133a74c1313708618e8060af0ffb9a -->

<!-- START_32ab14f98b405bec491803d51938b5fc -->
## api/LGUMain/business/businessBSP
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/businessBSP" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessBSP"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/businessBSP`


<!-- END_32ab14f98b405bec491803d51938b5fc -->

<!-- START_537779ec1eadfb37d1f8a5be036248d9 -->
## api/LGUMain/business/businessBSPPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessBSPPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessBSPPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessBSPPrint`


<!-- END_537779ec1eadfb37d1f8a5be036248d9 -->

<!-- START_a5e9452ea1daaa576ca60e6ee97879a0 -->
## api/LGUMain/business/businessBSPReport
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/businessBSPReport" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/businessBSPReport"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/businessBSPReport`


<!-- END_a5e9452ea1daaa576ca60e6ee97879a0 -->

<!-- START_5296d9a6c50d4f6d4801cf23e04be106 -->
## api/LGUMain/business/officeType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/officeType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/officeType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/officeType`


<!-- END_5296d9a6c50d4f6d4801cf23e04be106 -->

<!-- START_c4bbd4cfb00c3e44342d3dcc2276980e -->
## api/LGUMain/business/BSPType
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/BSPType" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/BSPType"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/BSPType`


<!-- END_c4bbd4cfb00c3e44342d3dcc2276980e -->

<!-- START_a6c35589aa40a84fa28430d96bf47861 -->
## api/LGUMain/business/getBusinesskind
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/getBusinesskind" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/getBusinesskind"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/getBusinesskind`


<!-- END_a6c35589aa40a84fa28430d96bf47861 -->

<!-- START_6ef594bd7c487240e3e3a74c51ae018e -->
## api/LGUMain/business/displaydtireport
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displaydtireport" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displaydtireport"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displaydtireport`


<!-- END_6ef594bd7c487240e3e3a74c51ae018e -->

<!-- START_2b144b5233f602f7d63787924170704d -->
## api/LGUMain/business/printDTIList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printDTIList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printDTIList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printDTIList`


<!-- END_2b144b5233f602f7d63787924170704d -->

<!-- START_5d25ff3baa252c48a4c02d3d80c5f7fe -->
## api/LGUMain/business/printMEI
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/printMEI" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/printMEI"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/printMEI`


<!-- END_5d25ff3baa252c48a4c02d3d80c5f7fe -->

<!-- START_4efc6316a1fb12adb7b7a5d548d51d92 -->
## api/LGUMain/business/displayzoningcertlist
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displayzoningcertlist" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displayzoningcertlist"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displayzoningcertlist`


<!-- END_4efc6316a1fb12adb7b7a5d548d51d92 -->

<!-- START_000ebabd4c14d889bd05bfad2dd0dacf -->
## api/LGUMain/business/displaybusinesslist
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displaybusinesslist" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displaybusinesslist"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displaybusinesslist`


<!-- END_000ebabd4c14d889bd05bfad2dd0dacf -->

<!-- START_3039fc400e4641c4674cbf0c7c59dec7 -->
## api/LGUMain/business/displaybrgylist
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displaybrgylist" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displaybrgylist"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displaybrgylist`


<!-- END_3039fc400e4641c4674cbf0c7c59dec7 -->

<!-- START_d0755b47217de93ca33ff2d2515954c3 -->
## api/LGUMain/business/displaycadastrallot
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displaycadastrallot" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displaycadastrallot"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displaycadastrallot`


<!-- END_d0755b47217de93ca33ff2d2515954c3 -->

<!-- START_7824cd92147c1fae553daf692ec5060b -->
## api/LGUMain/business/displaytaxdec
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displaytaxdec" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displaytaxdec"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displaytaxdec`


<!-- END_7824cd92147c1fae553daf692ec5060b -->

<!-- START_7a11655e96c486728a8145d226ef7dfb -->
## api/LGUMain/business/displayclassification
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displayclassification" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displayclassification"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displayclassification`


<!-- END_7a11655e96c486728a8145d226ef7dfb -->

<!-- START_573295c314e749e45d4c8a5e51309362 -->
## api/LGUMain/business/displaybillingfees
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/business/displaybillingfees" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/displaybillingfees"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/business/displaybillingfees`


<!-- END_573295c314e749e45d4c8a5e51309362 -->

<!-- START_73532a89c415066df9e62124058fef47 -->
## api/LGUMain/business/save
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/business/save" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/business/save"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/business/save`


<!-- END_73532a89c415066df9e62124058fef47 -->

<!-- START_64806286ec561414372081f432d8e137 -->
## api/LGUMain/rpt/getdelinquency
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/getdelinquency" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/getdelinquency"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/getdelinquency`


<!-- END_64806286ec561414372081f432d8e137 -->

<!-- START_b67f32364bbde897d37e49c7e96e8ec3 -->
## api/LGUMain/rpt/printLists
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/rpt/printLists" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/printLists"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/rpt/printLists`


<!-- END_b67f32364bbde897d37e49c7e96e8ec3 -->

<!-- START_babefe61d71596a6a263960171c7a27a -->
## api/LGUMain/rpt/generateBillComputation/{username}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/generateBillComputation/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/generateBillComputation/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/generateBillComputation/{username}`


<!-- END_babefe61d71596a6a263960171c7a27a -->

<!-- START_d289f130ec68c31e115313ba99d77fad -->
## api/LGUMain/rpt/getCollectionAbtract
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/getCollectionAbtract" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/getCollectionAbtract"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/getCollectionAbtract`


<!-- END_d289f130ec68c31e115313ba99d77fad -->

<!-- START_8a0c7be3e657dd240fa9dac80a364632 -->
## api/LGUMain/rpt/printrptabstract
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/rpt/printrptabstract" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/printrptabstract"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/rpt/printrptabstract`


<!-- END_8a0c7be3e657dd240fa9dac80a364632 -->

<!-- START_01c4a92a78975d324526d69a10bc0510 -->
## api/LGUMain/rpt/getRPTMasterlist
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/getRPTMasterlist" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/getRPTMasterlist"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/getRPTMasterlist`


<!-- END_01c4a92a78975d324526d69a10bc0510 -->

<!-- START_1d3d27c19a705e4781c9e97a16c8ee58 -->
## api/LGUMain/rpt/getRPTTaxDueandPayment/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/getRPTTaxDueandPayment/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/getRPTTaxDueandPayment/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/getRPTTaxDueandPayment/{id}`


<!-- END_1d3d27c19a705e4781c9e97a16c8ee58 -->

<!-- START_68d9dad59ccd6dbb3c585cca63d1d3bb -->
## api/LGUMain/rpt/printform/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/printform/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/printform/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/printform/{id}`


<!-- END_68d9dad59ccd6dbb3c585cca63d1d3bb -->

<!-- START_5b7e25fb525ed57200731e116f34cd5d -->
## api/LGUMain/rpt/printList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/rpt/printList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/printList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/rpt/printList`


<!-- END_5b7e25fb525ed57200731e116f34cd5d -->

<!-- START_1e9084db5b23236ea4aedc754ea73fb9 -->
## api/LGUMain/rpt/certificationList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/rpt/certificationList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/certificationList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/rpt/certificationList`


<!-- END_1e9084db5b23236ea4aedc754ea73fb9 -->

<!-- START_e88e7ab96a92f9b2fb1f6af58eb6a18c -->
## api/LGUMain/rpt/printCertification
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/rpt/printCertification" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/rpt/printCertification"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/rpt/printCertification`


<!-- END_e88e7ab96a92f9b2fb1f6af58eb6a18c -->

<!-- START_a637df70fb4b0d48ae08fa1e48959d46 -->
## api/LGUMain/Docreceived/showIncoming
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Docreceived/showIncoming" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Docreceived/showIncoming"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/LGUMain/Docreceived/showIncoming`


<!-- END_a637df70fb4b0d48ae08fa1e48959d46 -->

<!-- START_c7a22595181f09554b13f3bedaed3593 -->
## api/LGUMain/Docreceived/received
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/Docreceived/received" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Docreceived/received"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/Docreceived/received`


<!-- END_c7a22595181f09554b13f3bedaed3593 -->

<!-- START_cdeb1a5a86b19adc53624f711b15d73c -->
## api/LGUMain/Docreceived/return/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Docreceived/return/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Docreceived/return/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/LGUMain/Docreceived/return/{id}`


<!-- END_cdeb1a5a86b19adc53624f711b15d73c -->

<!-- START_832102e28683e1cf18c0b76b971f0aaf -->
## api/LGUMain/Docreceived/receivedList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Docreceived/receivedList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Docreceived/receivedList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/LGUMain/Docreceived/receivedList`


<!-- END_832102e28683e1cf18c0b76b971f0aaf -->

<!-- START_4f3d4bbcea455f5cf993520007e74f13 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/tbl_market_bill" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/tbl_market_bill"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/tbl_market_bill`


<!-- END_4f3d4bbcea455f5cf993520007e74f13 -->

<!-- START_a767e55185113777a3fe61881da33ce7 -->
## api/LGUMain/Market/display/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Market/display/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/display/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/Market/display/{id}`


<!-- END_a767e55185113777a3fe61881da33ce7 -->

<!-- START_4743d46732ceca3f12f9340134f7979b -->
## api/LGUMain/Market/marketDelinquency
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Market/marketDelinquency" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/marketDelinquency"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/Market/marketDelinquency`


<!-- END_4743d46732ceca3f12f9340134f7979b -->

<!-- START_ed7190b1094106e45db0ddbdd343d01f -->
## api/LGUMain/Market/marketDelinquencyPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/Market/marketDelinquencyPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/marketDelinquencyPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/Market/marketDelinquencyPrint`


<!-- END_ed7190b1094106e45db0ddbdd343d01f -->

<!-- START_ef2efb6b9a7e30cb1999f6d3e557b42d -->
## api/LGUMain/Market/building
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Market/building" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/building"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/Market/building`


<!-- END_ef2efb6b9a7e30cb1999f6d3e557b42d -->

<!-- START_b16c2beef7935299031bf66dc2c2b1e8 -->
## api/LGUMain/Market/subBuilding/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Market/subBuilding/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/subBuilding/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/Market/subBuilding/{id}`


<!-- END_b16c2beef7935299031bf66dc2c2b1e8 -->

<!-- START_c4b9de8566968739d0945fdafaedf0e1 -->
## api/LGUMain/Market/marketMasterlist
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/Market/marketMasterlist" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/marketMasterlist"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/LGUMain/Market/marketMasterlist`


<!-- END_c4b9de8566968739d0945fdafaedf0e1 -->

<!-- START_edc8681de2e5a175e5c35e8f65a71201 -->
## api/LGUMain/Market/marketMasterlistPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/Market/marketMasterlistPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/Market/marketMasterlistPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/Market/marketMasterlistPrint`


<!-- END_edc8681de2e5a175e5c35e8f65a71201 -->

<!-- START_c7fa4c629a00336ff5e01bed53a515e4 -->
## api/LGUMain/taxPayerReport/kindofBusiness
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/taxPayerReport/kindofBusiness" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/taxPayerReport/kindofBusiness"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/LGUMain/taxPayerReport/kindofBusiness`


<!-- END_c7fa4c629a00336ff5e01bed53a515e4 -->

<!-- START_907bf7e1b94bea476d19811d9314f71a -->
## api/LGUMain/taxPayerReport/TaxPayerReport
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/LGUMain/taxPayerReport/TaxPayerReport" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/taxPayerReport/TaxPayerReport"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/LGUMain/taxPayerReport/TaxPayerReport`


<!-- END_907bf7e1b94bea476d19811d9314f71a -->

<!-- START_5eb2d0794329dd472e6e44bd20c40280 -->
## api/LGUMain/taxPayerReport/taxpayerReportPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/LGUMain/taxPayerReport/taxpayerReportPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/LGUMain/taxPayerReport/taxpayerReportPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/LGUMain/taxPayerReport/taxpayerReportPrint`


<!-- END_5eb2d0794329dd472e6e44bd20c40280 -->

<!-- START_53be1e9e10a08458929a2e0ea70ddb86 -->
## Entry point for Laravue Dashboard

> Example request:

```bash
curl -X GET \
    -G "http://localhost/" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET /`


<!-- END_53be1e9e10a08458929a2e0ea70ddb86 -->

<!-- START_c8b587ed5b3a68eaa5b209e499c6504d -->
## api/Business/BusinessReport/businessPermitStatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/businessPermitStatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessPermitStatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/businessPermitStatus`


<!-- END_c8b587ed5b3a68eaa5b209e499c6504d -->

<!-- START_abee451b460f74994673e43209ca4eb7 -->
## api/Business/BusinessReport/businessPermitStatusPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessPermitStatusPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessPermitStatusPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessPermitStatusPrint`


<!-- END_abee451b460f74994673e43209ca4eb7 -->

<!-- START_16df866039973e09ce6cad71c037f44b -->
## api/Business/BusinessReport/businessPaymentStatus
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/businessPaymentStatus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessPaymentStatus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/businessPaymentStatus`


<!-- END_16df866039973e09ce6cad71c037f44b -->

<!-- START_2778e20c0b4ef5275b5ff5e1b7b3f323 -->
## api/Business/BusinessReport/businessPaymentStatusPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessPaymentStatusPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessPaymentStatusPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessPaymentStatusPrint`


<!-- END_2778e20c0b4ef5275b5ff5e1b7b3f323 -->

<!-- START_0153e8ca129196b94837f96045f07284 -->
## api/Business/BusinessReport/taxPayerReport
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/taxPayerReport" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/taxPayerReport"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/taxPayerReport`


<!-- END_0153e8ca129196b94837f96045f07284 -->

<!-- START_241ca0f1a2e1feb8ee85595675d31f72 -->
## api/Business/BusinessReport/taxPayerReportPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/taxPayerReportPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/taxPayerReportPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/taxPayerReportPrint`


<!-- END_241ca0f1a2e1feb8ee85595675d31f72 -->

<!-- START_339c97dd06d9979c6fff0fb84fc59596 -->
## api/Business/BusinessReport/businessEnterprise
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/businessEnterprise" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessEnterprise"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/businessEnterprise`


<!-- END_339c97dd06d9979c6fff0fb84fc59596 -->

<!-- START_f9950da142b275d2db6fa88cb1d4e490 -->
## api/Business/BusinessReport/businessEnterprisePrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessEnterprisePrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessEnterprisePrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessEnterprisePrint`


<!-- END_f9950da142b275d2db6fa88cb1d4e490 -->

<!-- START_a0bb3768bd2b5b88af8fb14d2416c371 -->
## api/Business/BusinessReport/businessDTIReport
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/businessDTIReport" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessDTIReport"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/businessDTIReport`


<!-- END_a0bb3768bd2b5b88af8fb14d2416c371 -->

<!-- START_ab28c6630b0137ef977d990f9612d9dc -->
## api/Business/BusinessReport/businessDTIReportPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessDTIReportPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessDTIReportPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessDTIReportPrint`


<!-- END_ab28c6630b0137ef977d990f9612d9dc -->

<!-- START_b50c5b8b22b0b97b7d4d4e20b27bf77d -->
## api/Business/BusinessReport/businessDTIReportPrintMEI
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessDTIReportPrintMEI" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessDTIReportPrintMEI"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessDTIReportPrintMEI`


<!-- END_b50c5b8b22b0b97b7d4d4e20b27bf77d -->

<!-- START_f1f40afaafe97ec61f98a7a05e421a43 -->
## api/Business/BusinessReport/businessBMBE
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/businessBMBE" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessBMBE"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/businessBMBE`


<!-- END_f1f40afaafe97ec61f98a7a05e421a43 -->

<!-- START_bf694037b8c33daa5b0690ada9308bfb -->
## api/Business/BusinessReport/businessBMBEPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessBMBEPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessBMBEPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessBMBEPrint`


<!-- END_bf694037b8c33daa5b0690ada9308bfb -->

<!-- START_1518fdcb97b3b25851912fd119ac182f -->
## api/Business/BusinessReport/businessBSP
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Business/BusinessReport/businessBSP" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessBSP"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Business/BusinessReport/businessBSP`


<!-- END_1518fdcb97b3b25851912fd119ac182f -->

<!-- START_ab60f672fcf1849027cf74850821025c -->
## api/Business/BusinessReport/businessBSPPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessBSPPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessBSPPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessBSPPrint`


<!-- END_ab60f672fcf1849027cf74850821025c -->

<!-- START_7c2ed5dd5974f648c318793f08405d8e -->
## api/Business/BusinessReport/businessBSPReport
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Business/BusinessReport/businessBSPReport" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Business/BusinessReport/businessBSPReport"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Business/BusinessReport/businessBSPReport`


<!-- END_7c2ed5dd5974f648c318793f08405d8e -->

<!-- START_9db79f6b8c0a2601a4b7a46d35516a44 -->
## api/Treasury/RealPropertyTax/getrptTaxMasterList
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Treasury/RealPropertyTax/getrptTaxMasterList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxMasterList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Treasury/RealPropertyTax/getrptTaxMasterList`


<!-- END_9db79f6b8c0a2601a4b7a46d35516a44 -->

<!-- START_0f2298ecce7022d9bd1cb6f00f918429 -->
## api/Treasury/RealPropertyTax/rptTaxMasterListPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/rptTaxMasterListPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/rptTaxMasterListPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/rptTaxMasterListPrint`


<!-- END_0f2298ecce7022d9bd1cb6f00f918429 -->

<!-- START_05930bdae263b0dcec321de6929072a4 -->
## api/Treasury/RealPropertyTax/getrptTaxClearance/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Treasury/RealPropertyTax/getrptTaxClearance/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxClearance/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Treasury/RealPropertyTax/getrptTaxClearance/{id}`


<!-- END_05930bdae263b0dcec321de6929072a4 -->

<!-- START_796b2ca1dc18bbfbb5773b6cb7ac8bb0 -->
## api/Treasury/RealPropertyTax/rptTaxClearancePrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/rptTaxClearancePrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/rptTaxClearancePrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/rptTaxClearancePrint`


<!-- END_796b2ca1dc18bbfbb5773b6cb7ac8bb0 -->

<!-- START_e4aa111384260daef1d0e8205c58f474 -->
## api/Treasury/RealPropertyTax/getrptTaxDelinquency
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDelinquency" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDelinquency"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/getrptTaxDelinquency`


<!-- END_e4aa111384260daef1d0e8205c58f474 -->

<!-- START_62b4923ed0d62bf312d6791f9b92879e -->
## api/Treasury/RealPropertyTax/rptTaxDelinquencyPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/rptTaxDelinquencyPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/rptTaxDelinquencyPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/rptTaxDelinquencyPrint`


<!-- END_62b4923ed0d62bf312d6791f9b92879e -->

<!-- START_778a70bcf5094a91fa505038ca0582d9 -->
## api/Treasury/RealPropertyTax/getrptCollectionAbtract
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Treasury/RealPropertyTax/getrptCollectionAbtract" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptCollectionAbtract"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Treasury/RealPropertyTax/getrptCollectionAbtract`


<!-- END_778a70bcf5094a91fa505038ca0582d9 -->

<!-- START_519b976e93b5b28208c7d917cf098ab5 -->
## api/Treasury/RealPropertyTax/rptCollectionAbtractPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/rptCollectionAbtractPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/rptCollectionAbtractPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/rptCollectionAbtractPrint`


<!-- END_519b976e93b5b28208c7d917cf098ab5 -->

<!-- START_55651821fd8bd26cab536381f2b51a8d -->
## api/Treasury/RealPropertyTax/getrptTaxDueandPayment/{id}
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDueandPayment/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDueandPayment/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "error": "Unauthorized"
}
```

### HTTP Request
`GET api/Treasury/RealPropertyTax/getrptTaxDueandPayment/{id}`


<!-- END_55651821fd8bd26cab536381f2b51a8d -->

<!-- START_9eb283a50c4bc5354473898fb28c916c -->
## api/Treasury/RealPropertyTax/getrptTaxDueDisplayList
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDueDisplayList" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDueDisplayList"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/getrptTaxDueDisplayList`


<!-- END_9eb283a50c4bc5354473898fb28c916c -->

<!-- START_f916ed7904b2ba79cf98636c2d1dbc3a -->
## api/Treasury/RealPropertyTax/getrptTaxDueandPaymentPrint
> Example request:

```bash
curl -X POST \
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDueandPaymentPrint" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/Treasury/RealPropertyTax/getrptTaxDueandPaymentPrint"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/Treasury/RealPropertyTax/getrptTaxDueandPaymentPrint`


<!-- END_f916ed7904b2ba79cf98636c2d1dbc3a -->



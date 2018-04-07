@symfony-extension
Feature: Symfony extension

  Scenario: An endpoint should be quickly usable
    Given I process the symfony extension
    And there is a public "my_service.method.method_name" JSON-RPC method service
    And there is a public "my_service.method.another" JSON-RPC method service
    And there is a public "my_service.method.dummy" JSON-RPC method service
    When I load endpoint from "json_rpc_http_server.endpoint" service
    And I inject my "my-method-name" to "my_service.method.method_name" JSON-RPC mapping into default method resolver instance
    # Bind same service a second time
    And I inject my "my-method-alias" to "my_service.method.method_name" JSON-RPC mapping into default method resolver instance
    And I inject my "an-another-method" to "my_service.method.another" JSON-RPC mapping into default method resolver instance
    And I inject my "getDummy" to "my_service.method.dummy" JSON-RPC mapping into default method resolver instance
    Then endpoint should respond to following JSON-RPC methods:
      | getDummy          |
      | my-method-name    |
      | an-another-method |
      | my-method-alias   |

  Scenario: An endpoint should be quickly usable also by using container injection
    Given I process the symfony extension
    And there is a public "my_service.method.method_name" JSON-RPC method service
    And there is a public "my_service.method.another" JSON-RPC method service
    And there is a public "my_service.method.dummy" JSON-RPC method service
    And I inject my "my-method-name" to "my_service.method.method_name" JSON-RPC mapping into default method resolver definition
    # Bind same service a second time
    And I inject my "my-method-alias" to "my_service.method.method_name" JSON-RPC mapping into default method resolver definition
    And I inject my "an-another-method" to "my_service.method.another" JSON-RPC mapping into default method resolver definition
    And I inject my "getDummy" to "my_service.method.dummy" JSON-RPC mapping into default method resolver definition
    When I load endpoint from "json_rpc_http_server.endpoint" service
    Then endpoint should respond to following JSON-RPC methods:
      | getDummy          |
      | my-method-name    |
      | an-another-method |
      | my-method-alias   |

  @symfony-method-resolver-tag
  Scenario: Use a custom method resolver
    Given I tag my custom method resolver service with "json_rpc_http_server.method_resolver"
    And I process the symfony extension
    When I load endpoint from "json_rpc_http_server.endpoint" service
    And I inject my "doSomething" JSON-RPC method into my custom method resolver instance
    And I inject my "doAnotherThing" JSON-RPC method into my custom method resolver instance
    And I inject my "doALastThing" JSON-RPC method into my custom method resolver instance
    Then endpoint should respond to following JSON-RPC methods:
      | doAnotherThing |
      | doALastThing   |
      | doSomething    |

  @symfony-method-resolver-tag
  Scenario: Use a custom method resolver with json-rpc methods container injection
    Given I tag my custom method resolver service with "json_rpc_http_server.method_resolver"
    And I process the symfony extension
    And I inject my "doSomething" JSON-RPC method into my custom method resolver definition
    And I inject my "doAnotherThing" JSON-RPC method into my custom method resolver definition
    And I inject my "doALastThing" JSON-RPC method into my custom method resolver definition
    When I load endpoint from "json_rpc_http_server.endpoint" service
    Then endpoint should respond to following JSON-RPC methods:
      | doAnotherThing |
      | doALastThing   |
      | doSomething    |

  @symfony-jsonrpc-method-tag
  Scenario: Define json-rpc method with tags
    Given I have a JSON-RPC method service definition with "json_rpc_http_server.jsonrpc_method" tag and following tag attributes:
    """
    {"method": "my-method-name"}
    """
    And I have a JSON-RPC method service definition with "json_rpc_http_server.jsonrpc_method" tag and following tag attributes:
    """
    {"method": "an-another-method"}
    """
    And I have a JSON-RPC method service definition with "json_rpc_http_server.jsonrpc_method" tag and following tag attributes:
    """
    {"method": "getDummy"}
    """
    And I process the symfony extension
    When I load endpoint from "json_rpc_http_server.endpoint" service
    Then endpoint should respond to following JSON-RPC methods:
      | getDummy          |
      | my-method-name    |
      | an-another-method |

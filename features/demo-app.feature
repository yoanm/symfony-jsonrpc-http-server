Feature: demo symfony application

  Scenario: Use Extension with default method resolver with JSON-RPC method tags
    # Ensure the two methods with tag have been loaded
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "methodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "MethodB", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """

  Scenario: Use Extension with default method resolver with JSON-RPC methods container injection
    # Ensure the two injected methods have been injected
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "getDummy", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "getAnotherDummy", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

    @yo1
  Scenario: Use Extension with custom method resolver
    Given I use my DemoApp custom method resolver
    # Ensure all methods have been loaded
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_methodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_methodB", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_methodC", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_methodD", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

  Scenario: Use Bundle with default method resolver with JSON-RPC method tags
    Given DemoApp will use JsonRpcHttpServerBundle
    # Ensure the two methods with tag have been loaded
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "method_A", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "Method_B", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """

  Scenario: Use Bundle with default method resolver with JSON-RPC methods container injection
    Given DemoApp will use JsonRpcHttpServerBundle
    # Ensure the two injected methods have been injected
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "get_dummy", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "get_another_dummy", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

  Scenario: Bundle with custom method resolver tag
    Given DemoApp will use JsonRpcHttpServerBundle
    And I use my DemoApp custom method resolver
    # Ensure all methods have been loaded
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_method_A", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_method_B", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_method_C", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "custom_method_D", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

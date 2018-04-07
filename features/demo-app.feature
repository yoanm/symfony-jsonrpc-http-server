Feature: demo symfony application

  @symfony-extension @symfony-jsonrpc-method-tag
  Scenario: Use Extension with default method resolver with JSON-RPC method tags
    # Ensure the two methods with tag have been loaded
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "defaultMethodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "defaultMethodAAlias", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":2}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "defaultMethodB", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":3}
    """

  @symfony-extension
  Scenario: Use Extension with default method resolver with JSON-RPC methods container injection
    # Ensure the two injected methods have been injected
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "defaultGetDummy", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "defaultGetAnotherDummy", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":2}
    """

  @symfony-extension @symfony-method-resolver-tag
  Scenario: Use Extension with custom method resolver
    Given I use my DemoApp custom method resolver
    # Ensure all methods have been loaded
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customMethodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customMethodB", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customMethodC", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on "/my-json-rpc-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customMethodD", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

  @symfony-bundle @symfony-jsonrpc-method-tag
  Scenario: Use Bundle with default method resolver with JSON-RPC method tags
    Given DemoApp will use JsonRpcHttpServerBundle
    # Ensure the two methods with tag have been loaded
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledMethodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledMethodAAlias", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":2}
    """
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledMethodB", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":3}
    """

  @symfony-bundle
  Scenario: Use Bundle with default method resolver with JSON-RPC methods container injection
    Given DemoApp will use JsonRpcHttpServerBundle
    # Ensure the two injected methods have been injected
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledGetDummy", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":1}
    """
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledGetAnotherDummy", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":2}
    """

  @symfony-bundle @symfony-method-resolver-tag
  Scenario: Bundle with custom method resolver tag
    Given DemoApp will use JsonRpcHttpServerBundle
    And I use my DemoApp custom method resolver
    # Ensure all methods have been loaded
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customBundledMethodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customBundledMethodB", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customBundledMethodC", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on "/json-rpc" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "customBundledMethodD", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

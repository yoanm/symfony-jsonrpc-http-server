Feature: demo symfony application

  Scenario: Default method resolver with JSON-RPC method tags
    # Ensure the two methods with tag have been loaded
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "methodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "MethodB", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """

  Scenario: Default method resolver with JSON-RPC methods container injection
    # Ensure the two injected methods have been loaded
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "getDummy", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "getAnotherDummy", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

  Scenario: custom method resolver
    Given I use my DemoApp custom method resolver
    # Ensure all methods have been loaded
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "custom_methodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "custom_methodB", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":2}
    """
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "custom_methodC", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodC", "id":3}
    """
    When I send following "POST" input on demoApp kernel:
    """
    {"jsonrpc": "2.0", "method": "custom_methodD", "id": 4}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodD", "id":4}
    """

Feature: demo symfony application

  Scenario: Check that all methods are available
    # Ensure the two methods with tag have been loaded
    When I send following "POST" input on "/my-custom-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledMethodA", "id": 1}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":1}
    """
    When I send following "POST" input on "/my-custom-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledMethodAAlias", "id": 2}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodA", "id":2}
    """
    When I send following "POST" input on "/my-custom-endpoint" demoApp kernel endpoint:
    """
    {"jsonrpc": "2.0", "method": "bundledMethodB", "id": 3}
    """
    Then I should have a "200" response from demoApp with following content:
    """
    {"jsonrpc":"2.0", "result":"MethodB", "id":3}
    """

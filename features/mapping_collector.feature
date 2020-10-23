Feature: Mapping collector

  Scenario: Check that configured mapping aware service have methods mapping
    Given I will use kernel with MappingCollector listener
    Then Collector should have "DemoApp\Method\MethodA" JSON-RPC method with name "bundledMethodA"
    And Collector should have "DemoApp\Method\MethodA" JSON-RPC method with name "bundledMethodA"
    And Collector should have "DemoApp\Method\MethodB" JSON-RPC method with name "bundledMethodB"
    And Collector should have "DemoApp\Method\MethodC" JSON-RPC method with name "bundledGetDummy"
    And Collector should have "DemoApp\Method\MethodD" JSON-RPC method with name "bundledGetAnotherDummy"

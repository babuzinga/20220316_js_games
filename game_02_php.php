<?php

// https://www.o2b.ru/%D0%BF%D0%BE%D0%B8%D1%81%D0%BA-%D1%81%D0%B0%D0%BC%D0%BE%D0%B3%D0%BE-%D0%B4%D0%B5%D1%88%D0%B5%D0%B2%D0%BE%D0%B3%D0%BE-%D0%BC%D0%B0%D1%80%D1%88%D1%80%D1%83%D1%82%D0%B0-%D0%BD%D0%B0-php/

interface NodeInterface
{
  /**
   * Connects the node to another $node.
   * A $distance, to balance the connection, can be specified.
   *
   * @param Node $node
   * @param integer $distance
   */
  public function connect(NodeInterface $node, $distance = 1);

  /**
   * Returns the connections of the current node.
   *
   * @return Array
   */
  public function getConnections();

  /**
   * Returns the identifier of this node.
   *
   * @return mixed
   */
  public function getId();

  /**
   * Returns node's potential.
   *
   * @return integer
   */
  public function getPotential();

  /**
   * Returns the node which gave to the current node its potential.
   *
   * @return Node
   */
  public function getPotentialFrom();

  /**
   * Returns whether the node has passed or not.
   *
   * @return boolean
   */
  public function isPassed();

  /**
   * Marks this node as passed, meaning that, in the scope of a graph, he
   * has already been processed in order to calculate its potential.
   */
  public function markPassed();

  /**
   * Sets the potential for the node, if the node has no potential or the
   * one it has is higher than the new one.
   *
   * @param integer $potential
   * @param Node $from
   * @return boolean
   */
  public function setPotential($potential, NodeInterface $from);
}

interface GraphInterface
{

  /**
   * Adds a new node to the current graph.
   *
   * @param Node $node
   * @return Graph
   * @throws Exception
   */
  public function add(NodeInterface $node);

  /**
   * Returns the node identified with the $id associated to this graph.
   *
   * @param mixed $id
   * @return Node
   * @throws Exception
   */
  public function getNode($id);

  /**
   * Returns all the nodes that belong to this graph.
   *
   * @return Array
   */
  public function getNodes();
}

class Graph implements GraphInterface
{
  /**
   * All the nodes in the graph
   *
   * @var array
   */
  protected $nodes = array();

  /**
   * Adds a new node to the current graph.
   *
   * @param Node $node
   * @return Graph
   * @throws Exception
   */
  public function add(NodeInterface $node)
  {
    if (array_key_exists($node->getId(), $this->getNodes())) {
      throw new Exception('Unable to insert multiple Nodes with the same ID in a Graph');
    }
    $this->nodes[$node->getId()] = $node;
    return $this;
  }

  /**
   * Returns the node identified with the $id associated to this graph.
   *
   * @param mixed $id
   * @return Node
   * @throws Exception
   */
  public function getNode($id)
  {
    $nodes = $this->getNodes();
    if (!array_key_exists($id, $nodes)) {
      throw new Exception("Unable to find $id in the Graph");
    }
    return $nodes[$id];
  }

  /**
   * Returns all the nodes that belong to this graph.
   *
   * @return Array
   */
  public function getNodes()
  {
    return $this->nodes;
  }
}

class Node implements NodeInterface
{
  protected $id;
  protected $potential;
  protected $potentialFrom;
  protected $connections = array();
  protected $passed = false;

  /**
   * Instantiates a new node, requiring a ID to avoid collisions.
   *
   * @param mixed $id
   */
  public function __construct($id)
  {
    $this->id = $id;
  }

  /**
   * Connects the node to another $node.
   * A $distance, to balance the connection, can be specified.
   *
   * @param Node $node
   * @param integer $distance
   */
  public function connect(NodeInterface $node, $distance = 1)
  {
    $this->connections[$node->getId()] = $distance;
  }

  /**
   * Returns the distance to the node.
   *
   * @return Array
   */
  public function getDistance(NodeInterface $node)
  {
    return $this->connections[$node->getId()];
  }

  /**
   * Returns the connections of the current node.
   *
   * @return Array
   */
  public function getConnections()
  {
    return $this->connections;
  }

  /**
   * Returns the identifier of this node.
   *
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Returns node's potential.
   *
   * @return integer
   */
  public function getPotential()
  {
    return $this->potential;
  }

  /**
   * Returns the node which gave to the current node its potential.
   *
   * @return Node
   */
  public function getPotentialFrom()
  {
    return $this->potentialFrom;
  }

  /**
   * Returns whether the node has passed or not.
   *
   * @return boolean
   */
  public function isPassed()
  {
    return $this->passed;
  }

  /**
   * Marks this node as passed, meaning that, in the scope of a graph, he
   * has already been processed in order to calculate its potential.
   */
  public function markPassed()
  {
    $this->passed = true;
  }

  /**
   * Sets the potential for the node, if the node has no potential or the
   * one it has is higher than the new one.
   *
   * @param integer $potential
   * @param Node $from
   * @return boolean
   */
  public function setPotential($potential, NodeInterface $from)
  {
    $potential = ( int )$potential;
    if (!$this->getPotential() || $potential < $this->getPotential()) {
      $this->potential = $potential;
      $this->potentialFrom = $from;
      return true;
    }
    return false;
  }
}

class Dijkstra
{
  protected $startingNode;
  protected $endingNode;
  protected $graph;
  protected $paths = array();
  protected $solution = false;

  /**
   * Instantiates a new algorithm, requiring a graph to work with.
   *
   * @param Graph $graph
   */
  public function __construct(Graph $graph)
  {
    $this->graph = $graph;
  }

  /**
   * Returns the distance between the starting and the ending point.
   *
   * @return integer
   */
  public function getDistance()
  {
    if (!$this->isSolved()) {
      throw new Exception("Cannot calculate the distance of a non-solved algorithm:\nDid you forget to call ->solve()?");
    }
    return $this->getEndingNode()->getPotential();
  }

  /**
   * Gets the node which we are pointing to.
   *
   * @return Node
   */
  public function getEndingNode()
  {
    return $this->endingNode;
  }

  /**
   * Returns the solution in a human-readable style.
   *
   * @return string
   */
  public function getLiteralShortestPath()
  {
    $path = $this->solve();
    $literal = '';
    foreach ($path as $p) {
      $literal .= "{$p->getId()} - ";
    }
    return substr($literal, 0, count($literal) - 4);
  }

  /**
   * Reverse-calculates the shortest path of the graph thanks the potentials
   * stored in the nodes.
   *
   * @return Array
   */
  public function getShortestPath()
  {
    $path = array();
    $node = $this->getEndingNode();
    while ($node->getId() != $this->getStartingNode()->getId()) {
      $path[] = $node;
      $node = $node->getPotentialFrom();
    }
    $path[] = $this->getStartingNode();
    return array_reverse($path);
  }

  /**
   * Retrieves the node which we are starting from to calculate the shortest path.
   *
   * @return Node
   */
  public function getStartingNode()
  {
    return $this->startingNode;
  }

  /**
   * Sets the node which we are pointing to.
   *
   * @param Node $node
   */
  public function setEndingNode(Node $node)
  {
    $this->endingNode = $node;
  }

  /**
   * Sets the node which we are starting from to calculate the shortest path.
   *
   * @param Node $node
   */
  public function setStartingNode(Node $node)
  {
    $this->paths[] = array($node);
    $this->startingNode = $node;
  }

  /**
   * Solves the algorithm and returns the shortest path as an array.
   *
   * @return Array
   */
  public function solve()
  {
    if (!$this->getStartingNode() || !$this->getEndingNode()) {
      throw new Exception("Cannot solve the algorithm without both starting and ending nodes");
    }
    $this->calculatePotentials($this->getStartingNode());
    $this->solution = $this->getShortestPath();
    return $this->solution;
  }

  /**
   * Recursively calculates the potentials of the graph, from the
   * starting point you specify with ->setStartingNode(), traversing
   * the graph due to Node's $connections attribute.
   *
   * @param Node $node
   */
  protected function calculatePotentials(Node $node)
  {
    $connections = $node->getConnections();
    $sorted = array_flip($connections);
    krsort($sorted);
    foreach ($connections as $id => $distance) {
      $v = $this->getGraph()->getNode($id);
      $v->setPotential($node->getPotential() + $distance, $node);
      foreach ($this->getPaths() as $path) {
        $count = count($path);
        if ($path[$count - 1]->getId() === $node->getId()) {
          $this->paths[] = array_merge($path, array($v));
        }
      }
    }
    $node->markPassed();
    // Get loop through the current node's nearest connections
    // to calculate their potentials.
    foreach ($sorted as $id) {
      $node = $this->getGraph()->getNode($id);
      if (!$node->isPassed()) {
        $this->calculatePotentials($node);
      }
    }
  }

  /**
   * Returns the graph associated with this algorithm instance.
   *
   * @return Graph
   */
  protected function getGraph()
  {
    return $this->graph;
  }

  /**
   * Returns the possible paths registered in the graph.
   *
   * @return Array
   */
  protected function getPaths()
  {
    return $this->paths;
  }

  /**
   * Checks wheter the current algorithm has been solved or not.
   *
   * @return boolean
   */
  protected function isSolved()
  {
    return ( bool )$this->solution;
  }
}


function printShortestPath($from_name, $to_name, $routes)
{
  $graph = new Graph();
  foreach ($routes as $route) {
    $from = $route['from'];
    $to = $route['to'];
    $price = $route['price'];
    if (!array_key_exists($from, $graph->getNodes())) {
      $from_node = new Node($from);
      $graph->add($from_node);
    } else {
      $from_node = $graph->getNode($from);
    }
    if (!array_key_exists($to, $graph->getNodes())) {
      $to_node = new Node($to);
      $graph->add($to_node);
    } else {
      $to_node = $graph->getNode($to);
    }
    $from_node->connect($to_node, $price);
  }

  $g = new Dijkstra($graph);
  $start_node = $graph->getNode($from_name);
  $end_node = $graph->getNode($to_name);
  $g->setStartingNode($start_node);
  $g->setEndingNode($end_node);
  echo "From: " . $start_node->getId() . "\n";
  echo "To: " . $end_node->getId() . "\n";
  echo "Route: " . $g->getLiteralShortestPath() . "\n";
  echo "Total: " . $g->getDistance() . "\n";
}

$routes = array();
$routes[] = array('from' => 'a', 'to' => 'b', 'price' => 100);
$routes[] = array('from' => 'c', 'to' => 'd', 'price' => 300);
$routes[] = array('from' => 'b', 'to' => 'c', 'price' => 200);
$routes[] = array('from' => 'a', 'to' => 'd', 'price' => 900);
$routes[] = array('from' => 'b', 'to' => 'd', 'price' => 300);

printShortestPath('a', 'd', $routes);

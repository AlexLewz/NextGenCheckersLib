<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ShNode {
	private $x;
	private $y;
	private $value;
	private $directTopLeft;
	private $directTopRight;
	private $directBottomLeft;
	private $directBottomRight;
/*
	public function __construct($value)
	{
		$this->value = $value;
	}*/

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function setTopLeft($topLeft)
	{
		$this->directTopLeft = $topLeft;
		return $this;
	}

	public function setTopRight($topRight)
	{
		$this->directTopRight = $topRight;
		return $this;
	}

	public function setBottomRight($BottomRight)
	{
		$this->directBottomRight = $BottomRight;
		return $this;
	}

	public function setBottomLeft($BottomLeft)
	{
		$this->directBottomLeft = $BottomLeft;
		return $this;
	}

	public function setX($x)
	{
		$this->x = $x;
		return $this;
	}

	public function setY($y)
	{
		$this->y = $y;
		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}


	public function getX()
	{
		return $this->x;
	}

	public function getY()
	{
		return $this->y;
	}

	public function getTopLeft()
	{
		return $this->directTopLeft;
	}

	public function getTopRight()
	{
		return $this->directTopRight;
	}

	public function getBottomRight()
	{
		return $this->directBottomRight;
	}

	public function getBottomLeft()
	{
		return $this->directBottomLeft;
	}
}

function invertToNodes(&$doska_array)
{
	$nodes = array();

	for($x = 0, $b = 1; $x < 8; $x++, $b = ($b+1)%2)
	{
		for($y = $b; $y < 8; $y+=2)
		{
				$nodes[$x.$y] = (new ShNode())
										->setValue(''.$doska_array[$x][$y])
										->setY($y)
										->setX($x);
		}
	}

	for($x = 0, $b = 1; $x < 8; $x++, $b = ($b+1)%2)
	{
		for($y = $b; $y < 8; $y+=2)
		{
				$nodes[$x.$y]->setTopLeft(isValidConstraints($x-1, $y-1)?$nodes[($x-1).($y-1)]:null);
				$nodes[$x.$y]->setBottomRight(isValidConstraints($x+1, $y+1)?$nodes[($x+1).($y+1)]:null);
				$nodes[$x.$y]->setTopRight(isValidConstraints($x+1, $y-1)?$nodes[($x+1).($y-1)]:null);
				$nodes[$x.$y]->setBottomLeft(isValidConstraints($x-1, $y+1)?$nodes[($x-1).($y+1)]:null);
		}
	}

	return $nodes;
}

function isValidNextNode($next1, $next2)
{
	if($next1 != null) {
		if($next1->getValue() != 6) {
			if($next2 != null) {
				if($next2->getValue() == 6) {
					if($next2->getTopRight() != null)
					{
						if($next2->getTopRight()->getValue() != 6 && $next2->getTopRight() != $next1)
						{
							return true;
						}
					}
					if($next2->getTopLeft() != null)
					{
						if($next2->getTopLeft()->getValue() != 6 && $next2->getTopLeft() != $next1)
						{
							return true;
						}
					}
					if($next2->getBottomRight() != null)
					{
						if($next2->getBottomRight()->getValue() != 6 && $next2->getBottomRight() != $next1)
						{
							return true;
						}
					}
					if($next2->getBottomLeft() != null)
					{
						if($next2->getBottomLeft()->getValue() != 6 && $next2->getBottomLeft() != $next1)
						{
							return true;
						}
					}
				}
			}
		}
	}
	return false;
}

function getDirectionNode($node_last, $node_past)
{ // two last nodes
	if($node_last != null && $node_last->getTopRight() != null) 
	{	if($node_last->getTopRight() == $node_past)
			return $node_last->getBottomLeft();
	}
	if($node_last != null && $node_last->getBottomLeft() != null) 
	{	if($node_last->getBottomLeft() == $node_past)
			return $node_last->getTopRight();
	}
	if($node_last != null && $node_last->getTopLeft() != null) 
	{	if($node_last->getTopLeft() == $node_past)
			return $node_last->getBottomRight();
	}
	if($node_last != null && $node_last->getBottomRight() != null) 
	{	if($node_last->getBottomRight() == $node_past)
			return $node_last->getTopLeft();
	}

	return null;
}
/*function getDirectionName($node_last, $node_past)
{ // two last nodes
	if($node_last->getTopRight() == $node_past)
	{
		return 1;
	}
	if($node_last->getBottomLeft() == $node_past)
	{
		return 9;
	}
	if($node_last->getTopLeft() == $node_past)
	{
		return 3;
	}
	if($node_last->getBottomRight() == $node_past)
	{
		return 7;
	}
	return 0;
}*/
function isValidTurn($src, $dst)
{   // 1. мы можем ходить только по диагоналям
	// ноды уже представляют каждую ячейку диагонали (32 штуки)
	// 2. мы можем поставить шашку только на пустые клетки (==6)
	// 3. мы можем ходить только своими шашками ('это проверяется уровем выше на $src')
	// мы не можем выбрать чужие шашки (кстати)!
	// т.е. до сюда не дойдёт если не ваш ход. выделенная вначале и будет $src
	// мы можем ходить между тремя ячейками за раз или до конца поля если оно свободно и мы дамка
	// и эти ячейки расположены в одном направлении
	global $flat_found;
	$local_flat_found = array();
	if($dst->getValue() != 6) return false;
	if($src->getValue() == 6) return false;
	// теперь проверим что ход произведён из src в dst строго по одной диагонали (и в одном направлении)
	// с шашками мы знаем точно что с src до dst 2 ячейки или 3 если мы перепрыгиваем
	// с дамками мы точно знаем что с src до dst от 2 до 8 ячеек 
	// если 1 ячейка то сами себя клацнули, это обрабатываем выше уровнем)
	if(!findRoute($local_flat_found, $src, $dst)) return false; // мы не можем отходить от диагонали
	$flat_found = $local_flat_found;
	$route_len = count($flat_found);

	// мы ещё и дамка??
	if(($src->getValue() == 3 || $src->getValue() == 4)) {

		$noise = 0;
		foreach ($flat_found as $value) {
			if($value->getValue() != 6) $noise++;
		}
		if($noise == 1) return true;
		if($noise == 2) 
		{
			if($flat_found[$route_len-2]->getValue() != 6) { // проверяем что перед нами была шашка
					return true;
			}
		}
	}

	if($route_len == 3) {
		if($flat_found[$route_len-2]->getValue() != 6) { // проверяем что перед нами была шашка
			return true;
		}
	}

 	// мы уже проверили что src шашка и dst свободна
	if($route_len == 2) {
		if( $src->getValue() == 2 && (getVector($src, $dst) == 7 || getVector($src, $dst) == 9) ) {
			return true;
		}
		if( $src->getValue() == 1 && (getVector($src, $dst) == 1 || getVector($src, $dst) == 3) ) {	
			return true;
		}
	}
	return false; // цель - дать добро на смену src to dst
}

function findRoute(&$flat_found, $src, $dst)
{
	//global $flat_found;
	$vector = $src;
	$vector_num = getVector($src, $dst);
	
	while ($vector != null) {
		$flat_found[] = $vector;

		if($vector == $dst) {
			return true;
		}

		switch($vector_num)
		{
			case 7:
				$vector = $vector->getTopLeft();
			break;
			case 3:
				$vector = $vector->getBottomRight();
			break;
			case 9:
				$vector = $vector->getTopRight();
			 break;
			case 1: 
				$vector = $vector->getBottomLeft();
			break;
			case 0:
				$vector = null;
			break;
		}
	}

	return false;
}

function getVector($src, $dst)
{
	if($src->getX() > $dst->getX() && $src->getY() > $dst->getY())
		return 7;

	if($src->getX() < $dst->getX() && $src->getY() > $dst->getY())
		return 9;

	if($src->getX() > $dst->getX() && $src->getY() < $dst->getY())
		return 1;

	if($src->getX() < $dst->getX() && $src->getY() < $dst->getY())
		return 3;

	return 0;
}

function isValidAroundTurn($node)
{
	$topLeft = $node->getTopLeft();
	if($topLeft != null && $topLeft->getValue() != 6) {
		$topLeft = $topLeft->getTopLeft();
		if($topLeft != null && $topLeft->getValue() == 6) {
			return true;
		}
	}
	$topRight = $node->getTopRight();
	if($topRight != null && $topRight->getValue() != 6) {
		$topRight = $topRight->getTopRight();
		if($topRight != null && $topRight->getValue() == 6) {
			return true;
		}
	}
	$bottomLeft = $node->getBottomLeft();
	if($bottomLeft != null && $bottomLeft->getValue() != 6) {
		$bottomLeft = $bottomLeft->getBottomLeft();
		if($bottomLeft != null && $bottomLeft->getValue() == 6) {
			return true;
		}
	}
	$bottomRight = $node->getBottomRight();
	if($bottomRight != null && $bottomRight->getValue() != 6) {
		$bottomRight = $bottomRight->getBottomRight();
		if($bottomRight != null && $bottomRight->getValue() == 6) {
			return true;
		}
	}
	return false;
}

function isValidAround($node, $enemy_value)
{
	$topLeft = $node->getTopLeft();
	if($topLeft != null && $topLeft->getValue() != 6) {
		
	}
	$topRight = $node->getTopRight();
	if($topRight != null && $topRight->getValue() != 6) {

	}
	$bottomLeft = $node->getBottomLeft();
	if($bottomLeft != null && $bottomLeft->getValue() != 6) {

	}
	$bottomRight = $node->getBottomRight();
	if($bottomRight != null && $bottomRight->getValue() != 6) {

	}
}

function isUnLockedCell($src, $dst)
{
	global $flat_lock;

	if(count($flat_lock) == 0) return true; // у нас должна быть возможность ходить даже если некого кушать

	foreach ($flat_lock as $k => $v) {
		if($k == ''.$src->getX().$src->getY().'' && $v == $dst) return true;
	}
	return false;
}

function isUnLockedCellExist($dst)
{
	global $flat_lock;

	foreach ($flat_lock as $v) {
		if($v == $dst) return true;
	}
	return false;
}

function lockedCells($graph, $sashka, $damka, $vragsashka, $vragdamka)
{
	global $flat_lock;
	global $flat_found;

	$local_flat_lock = array();

	foreach ($graph as $c)
	{
		if($c->getValue() == $sashka || $c->getValue() == $damka)
		{ // now we have myselcell... as $c
			foreach ($graph as $t)
			{
				if($t->getValue() == 6)
				{ // now we have target selection ($_GET['CELL']) as $t
					if(isValidTurn($c, $t))
					{	// now we have an array of passed cells
						$bad_cell = null; // we can eat only one enemy in one turn
						foreach ($flat_found as $v)
						{	// and in it array we are find our bad cells
							if($v->getValue() == $vragsashka || $v->getValue() == $vragdamka)
							{	// and add it to collection
								$bad_cell = $v;
							}
						}
						if($bad_cell != null)
						{ // we found bad cell and we save src and dst params for recogite it
							$local_flat_lock[$c->getX().$c->getY()] = $flat_found[count($flat_found)-1];
						}
					}
				}
			}
		}
	}

	$flat_lock = $local_flat_lock;
}
?>

<?php
class Graph
{
  protected $graph;
  protected $visited = array();
 
  public function __construct($graph) {
    $this->graph = $graph;
  }
 
  // найдем минимальное число прыжков (связей) между 2 узлами

  public function breadthFirstSearch($origin, $destination) {
    // пометим все узлы как непосещенные
    foreach ($this->graph as $vertex => $adj) {
      $this->visited[$vertex] = false;
    }
 
    // пустая очередь
    $q = new SplQueue();
 
    // добавим начальную вершину в очередь и пометим ее как посещенную
    $q->enqueue($origin);
    $this->visited[$origin] = true;
 
    // это требуется для записи обратного пути от каждого узла
    $path = array();
    $path[$origin] = new SplDoublyLinkedList();
    $path[$origin]->setIteratorMode(
      SplDoublyLinkedList::IT_MODE_FIFO|SplDoublyLinkedList::IT_MODE_KEEP
    );
 
    $path[$origin]->push($origin);
 
    $found = false;
    // пока очередь не пуста и путь не найден
    while (!$q->isEmpty() && $q->bottom() != $destination) {
      $t = $q->dequeue();
 
      if (!empty($this->graph[$t])) {
        // для каждого соседнего узла

        if ($this->graph[$t]->getTopLeft() != null) {
        	$vertex = $this->graph[$t]->getTopLeft()->getX()
          			  .
      				  $this->graph[$t]->getTopLeft()->getY();

          if (!$this->visited[$vertex]) {
            // если все еще не посещен, то добавим в очередь и отметим
            $q->enqueue($vertex);
            $this->visited[$vertex] = true;
            // добавим узел к текущему пути
            $path[$vertex] = clone $path[$t];
            $path[$vertex]->push($vertex);
          }
        }

        if ($this->graph[$t]->getBottomRight() != null) {
        	$vertex = $this->graph[$t]->getBottomRight()->getX()
          			  .
      				  $this->graph[$t]->getBottomRight()->getY();

          if (!$this->visited[$vertex]) {
            // если все еще не посещен, то добавим в очередь и отметим
            $q->enqueue($vertex);
            $this->visited[$vertex] = true;
            // добавим узел к текущему пути
            $path[$vertex] = clone $path[$t];
            $path[$vertex]->push($vertex);
          }
        }

        if ($this->graph[$t]->getTopRight() != null) {
        	$vertex = $this->graph[$t]->getTopRight()->getX()
          			  .
      				  $this->graph[$t]->getTopRight()->getY();

          if (!$this->visited[$vertex]) {
            // если все еще не посещен, то добавим в очередь и отметим
            $q->enqueue($vertex);
            $this->visited[$vertex] = true;
            // добавим узел к текущему пути
            $path[$vertex] = clone $path[$t];
            $path[$vertex]->push($vertex);
          }
        }

        if ($this->graph[$t]->getBottomLeft() != null) {
        	$vertex = $this->graph[$t]->getBottomLeft()->getX()
          			  .
      				  $this->graph[$t]->getBottomLeft()->getY();

          if (!$this->visited[$vertex]) {
            // если все еще не посещен, то добавим в очередь и отметим
            $q->enqueue($vertex);
            $this->visited[$vertex] = true;
            // добавим узел к текущему пути
            $path[$vertex] = clone $path[$t];
            $path[$vertex]->push($vertex);
          }
        }


      }
    }
 
    if (isset($path[$destination])) {

      return $path[$destination];
    }
    
    return array();
  }
}

?>
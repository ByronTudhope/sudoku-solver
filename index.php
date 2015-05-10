<?php 
/**
* Made by Byron Tudhope, byrontudhope@gmail.com
*/

require 'sudoku.php';

$size = 9;
/*Hard*/
/*$sudoku_board = array(

        array(7,0,3,0,9,0,0,0,0),
        array(6,8,0,0,1,5,0,0,0),
        array(1,0,0,2,0,0,0,7,0),
        array(8,3,0,0,0,0,0,0,0),
        array(0,7,4,0,0,0,5,1,0),
        array(0,0,0,0,0,0,0,4,7),
        array(0,1,0,0,0,2,0,0,6),
        array(0,0,0,9,4,0,0,8,5),
        array(0,0,0,0,6,0,1,0,3)
    );*/

/*World's Hardest Sudoku*/
$sudoku_board = array(

        array(8,0,0,0,0,0,0,0,0),
        array(0,0,3,6,0,0,0,0,0),
        array(0,7,0,0,9,0,2,0,0),
        array(0,5,0,0,0,7,0,0,0),
        array(0,0,0,0,4,5,7,0,0),
        array(0,0,0,1,0,0,0,3,0),
        array(0,0,1,0,0,0,0,6,8),
        array(0,0,8,5,0,0,0,1,0),
        array(0,9,0,0,0,0,4,0,0)
    );

$board = Sudoku::make_board($sudoku_board, $size);

$sudoku = new Sudoku($size);

$sudoku->set_board($board);

$board = $sudoku->get_board();

echo('<table border="1">');
for ($i=0; $i < count($board); $i++) 
{
  echo('<tr>');
  for ($j=0; $j < count($board[$i]); $j++) 
  { 
    $eco_val = $board[$i][$j]->value;
    $eco_val = ($eco_val == 0) ? ' ' : $eco_val ;
    echo('<td>&nbsp;' . $eco_val . '&nbsp;</td>');
  }
  echo('</tr>');
}

echo('</table><br>');

$solved = $sudoku->solve();
$iterations = $sudoku->get_iterations();

echo ($solved) ? 'SOLVED in '.$iterations.' iterations' : 'TOO DIFFICULT FOR ME' ;

echo '<br><br>';

$solved_board = $sudoku->get_board();

if ($solved) 
{
    echo('<table border="1">');
    for ($i=0; $i < count($solved_board); $i++) 
    {
      echo('<tr>');
      for ($j=0; $j < count($solved_board[$i]); $j++) 
      { 
        $eco_val = $solved_board[$i][$j]->value;
        $eco_val = ($eco_val == 0) ? ' ' : $eco_val ;
        echo('<td>&nbsp;' . $eco_val . '&nbsp;</td>');
      }
      echo('</tr>');
    }

    echo('</table>');
}
else
{
    echo('<table border="1">');
    for ($i=0; $i < count($solved_board); $i++) 
    {
      echo('<tr>');
      for ($j=0; $j < count($solved_board[$i]); $j++) 
      { 
        $eco_val = $solved_board[$i][$j]->value;
        $eco_val = ($eco_val == 0) ? '('.implode(', ', $solved_board[$i][$j]->possibilities).')' : $eco_val ;
        echo('<td>&nbsp;' . $eco_val . '&nbsp;</td>');
      }
      echo('</tr>');
    }

    echo('</table>');
}

?>
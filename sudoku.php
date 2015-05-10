<?php 

/**
* Made by Byron Tudhope, byrontudhope@gmail.com
*/
class Sudoku 
{
    private $size;
    private $possibilities = array();
    private $total_places;
    private $changes = 0;
    private $iterations = 0;
    private $solved = FALSE;
    private $possible = TRUE;
    private $board = array();
    private $rows = array();
    private $cols = array();
    private $blocks = array();

    function __construct($size)
    {
        $this->size = $size;
        $this->total_places = $size * $size;
        for ($i=0; $i < $size; $i++) 
        {
            $this->possibilities[] = $i + 1;
        }
    }

    public function set_iterations($iterations)
    {
        $this->iterations = $iterations;
    }

    public function get_iterations()
    {
        return $this->iterations;
    }

    public static function make_board($sudoku_board, $size)
    {
        $board = array();
        for ($i=0; $i < $size; $i++) 
        { 
            for ($j=0; $j < $size; $j++) 
            { 
                $board[$i][$j] = new Place($i + 1, $j + 1, $sudoku_board[$i][$j]);
            }
        }
        return $board;
    }

    public function set_board($board)
    {

        $this->board = $board;
        $row_count = 0;
        foreach ($board as $row) 
        {
            $row_count++;
            $place_count = 0;
            foreach ($row as $place) 
            {
                $place_count++;
            }
            if ($place_count != $this->size) 
            {
                throw new Exception('Wrong Number of Columns in Row: '.$row_count, 1);
            }
        }
        if ($row_count != $this->size) 
        {
            throw new Exception('Wrong Number of Rows', 1);
        }

        $this->set_rows($board);
        $this->set_cols($board);
        $this->set_blocks($board);
    }

    public function get_board()
    {
        return $this->board;
    }

    public function solve($itr = 0)
    {
        $solved = 0;
        $unsolved = 0;

        $this->changes = 0;

        $this->iterations++;

        foreach ($this->board as $row) 
        {
            foreach ($row as $place) 
            {
                $this->solve_position($place->row, $place->col);
                $this->solve_pos_row($place->row, $place->col);
                $this->solve_pos_col($place->row, $place->col);
                $this->solve_pos_block($place->row, $place->col);
            }

            foreach ($row as $place) 
            {
                if ($place->value != 0)
                {
                    $solved++;
                }
                else
                {
                    $unsolved++;
                }
            }
        }

        if ($unsolved == 0) 
        {
            return TRUE;
        }
        else
        {
            if ($this->changes == 0)
            {
                $pos_with_least_options = $this->get_least_options();

                foreach ($pos_with_least_options->possibilities as $possibility) 
                {
                    try {
                        $sudoku = new Sudoku($this->size);
                        $sudoku->set_board($this->board);
                        $sudoku->set_position($pos_with_least_options->row, $pos_with_least_options->col, $possibility);
                        $sudoku->set_iterations($this->iterations);
                        $solved = $sudoku->solve();
                        if ($solved) 
                        {
                            $this->iterations = $sudoku->get_iterations();
                            $this->board = $sudoku->get_board();
                            return TRUE;
                        }
                        else
                        {
                            $this->iterations = $sudoku->get_iterations();
                        }
                    } 
                    catch (Exception $e) 
                    {
                        $this->iterations = $sudoku->get_iterations();
                    }
                }
                return FALSE;
            }
            return $this->solve($itr);
        }
    }

    private function solve_position($row, $col)
    {
        $board = $this->board;

        $place = $board[$row - 1][$col - 1];
        $val = $place->value;

        $row_arr = array();
        foreach ($this->rows[$row] as $place) 
        {
            $row_arr[] = $place->value;
        }

        $col_arr = array();
        foreach ($this->cols[$col] as $place) 
        {
            $col_arr[] = $place->value;
        }

        $block_arr = array();
        foreach ($this->blocks[$this->get_block($row, $col)] as $place) 
        {
            $block_arr[] = $place->value;
        }

        $place->possibilities = array();

        if ($val != 0) 
        {
            $test_row_arr = count($row_arr);
            $test_col_arr = count($col_arr);
            $test_block_arr = count($block_arr);

            if ($test_row_arr[$val] > 1)
            {
                print '<pre>';
                print_r($row_arr);
                print '</pre>';
                throw new Exception("Invalid Value {$val} in position: ({$row}, {$col}) (Row)", 1);
            }
            if ($test_col_arr[$val] > 1)
            {
                print '<pre>';
                print_r($col_arr);
                print '</pre>';
                throw new Exception("Invalid Value {$val} in position: ({$row}, {$col}) (Col)", 1);
            }
            if ($test_block_arr[$val] > 1)
            {
                print '<pre>';
                print_r($block_arr);
                print '</pre>';
                throw new Exception("Invalid Value {$val} in position: ({$row}, {$col}) (Block)", 1);
            }
            return TRUE;
        }
        else
        {
            $possibilities = array();

            foreach ($this->possibilities as $possible_value) 
            {
                $in_row = FALSE;
                $in_col = FALSE;
                $in_block = FALSE;

                if (in_array($possible_value, $row_arr)) 
                {
                    $in_row = TRUE;
                }
                if (in_array($possible_value, $col_arr)) 
                {
                    $in_col = TRUE;
                }
                if (in_array($possible_value, $block_arr)) 
                {
                    $in_block = TRUE;
                }
                if ($in_row == FALSE && $in_col == FALSE && $in_block == FALSE) 
                {
                    $possibilities[] = $possible_value;
                }
            }

            switch (count($possibilities)) 
            {
                case 0:
                    throw new Exception("Impossible Sudoku, No Value Possible for ({$row}, {$col})", 1);
                    break;
                case 1:
                    return $this->set_position($row, $col, $possibilities[0]);
                    break;
                default:
                    $this->set_possibilities($row, $col, $possibilities);
                    return FALSE;
                    break;
            }
        }

    }

    private function get_possibilities($row, $col)
    {
        $board = $this->board;

        $place = $board[$row - 1][$col - 1];

        if ($place->value != 0) 
        {
            return array();
        }

        $val = $place->value;

        $row_arr = array();
        foreach ($this->rows[$row] as $place) 
        {
            $row_arr[] = $place->value;
        }

        $col_arr = array();
        foreach ($this->cols[$col] as $place) 
        {
            $col_arr[] = $place->value;
        }

        $block_arr = array();
        foreach ($this->blocks[$this->get_block($row, $col)] as $place) 
        {
            $block_arr[] = $place->value;
        }

        $possibilities = array();

        foreach ($this->possibilities as $possible_value) 
        {
            $in_row = FALSE;
            $in_col = FALSE;
            $in_block = FALSE;

            if (in_array($possible_value, $row_arr)) 
            {
                $in_row = TRUE;
            }
            if (in_array($possible_value, $col_arr)) 
            {
                $in_col = TRUE;
            }
            if (in_array($possible_value, $block_arr)) 
            {
                $in_block = TRUE;
            }
            if ($in_row == FALSE && $in_col == FALSE && $in_block == FALSE) 
            {
                $possibilities[] = $possible_value;
            }
        }

        return $possibilities;

    }

    private function check_position($row, $col, $value)
    {
        $board = $this->board;
        $place = $board[$row - 1][$col - 1];
        if ($place->value != 0) 
        {
            return FALSE;
        }
        $row_arr = array();
        foreach ($this->rows[$row] as $place) 
        {
            $row_arr[] = $place->value;
        }
        $col_arr = array();
        foreach ($this->cols[$col] as $place) 
        {
            $col_arr[] = $place->value;
        }
        $block_arr = array();
        foreach ($this->blocks[$this->get_block($row, $col)] as $place) 
        {
            $block_arr[] = $place->value;
        }
        $in_row = FALSE;
        $in_col = FALSE;
        $in_block = FALSE;
        if (in_array($value, $row_arr)) 
        {
            $in_row = TRUE;
        }
        if (in_array($value, $col_arr)) 
        {
            $in_col = TRUE;
        }
        if (in_array($value, $block_arr)) 
        {
            $in_block = TRUE;
        }
        if ($in_row == FALSE && $in_col == FALSE && $in_block == FALSE) 
        {
            return TRUE;
        }
        return FALSE;
    }

    private function solve_pos_row($row, $col)
    {
        $board = $this->board;
        $place = $board[$row - 1][$col - 1];
        $val = $place->value;
        if ($val != 0) 
        {
            return FALSE;
        }

        $row_arr = $this->rows[$row];

        $possibilities = $this->get_possibilities($row, $col);

        $row_possibilities = array();

        foreach ($row_arr as $row_place_tmp) 
        {

            if ($row_place_tmp->value == 0) 
            {
                if ($row_place_tmp->row != $place->row || $row_place_tmp->col != $place->col) 
                {
                    $place_possibilities = $this->get_possibilities($row_place_tmp->row, $row_place_tmp->col);
                    

                    foreach ($place_possibilities as $pos_val) 
                    {
                        $row_possibilities[$pos_val] = $pos_val;
                    }
                }
            }
        }

        $found = TRUE;
        $new_val = 0;

        foreach ($possibilities as $pos1) 
        {
            if (!in_array($pos1, $row_possibilities))
            {
                $found = FALSE;
                $new_val = $pos1;
            }
        }

        if (!$found) 
        {
            return $this->set_position($row, $col, $new_val);
        }

    }

    private function solve_pos_col($row, $col)
    {
        $board = $this->board;
        $place = $board[$row - 1][$col - 1];
        $val = $place->value;
        if ($val != 0) 
        {
            return FALSE;
        }

        $col_arr = $this->cols[$col];

        $possibilities = $this->get_possibilities($row, $col);

        $col_possibilities = array();

        foreach ($col_arr as $col_place_tmp) 
        {

            if ($col_place_tmp->value == 0) 
            {
                if ($col_place_tmp->row != $place->row || $col_place_tmp->col != $place->col) 
                {
                    $place_possibilities = $this->get_possibilities($col_place_tmp->row, $col_place_tmp->col);
                    

                    foreach ($place_possibilities as $pos_val) 
                    {
                        $col_possibilities[$pos_val] = $pos_val;
                    }
                }
            }
        }

        $found = TRUE;
        $new_val = 0;

        foreach ($possibilities as $pos1) 
        {
            if (!in_array($pos1, $col_possibilities))
            {
                $found = FALSE;
                $new_val = $pos1;
            }
        }

        if (!$found) 
        {
            return $this->set_position($row, $col, $new_val);
        }
    }

    private function solve_pos_block($row, $col)
    {

        $board = $this->board;
        $place = $board[$row - 1][$col - 1];
        $val = $place->value;
        if ($val != 0) 
        {
            return FALSE;
        }

        $block_num = $this->get_block($row, $col);
        $col_arr = $this->blocks[$block_num];

        $possibilities = $this->get_possibilities($row, $col);

        $col_possibilities = array();

        foreach ($col_arr as $col_place_tmp) 
        {

            if ($col_place_tmp->value == 0) 
            {
                if ($col_place_tmp->row != $place->row || $col_place_tmp->col != $place->col) 
                {
                    $place_possibilities = $this->get_possibilities($col_place_tmp->row, $col_place_tmp->col);
                    

                    foreach ($place_possibilities as $pos_val) 
                    {
                        $col_possibilities[$pos_val] = $pos_val;
                    }
                }
            }
        }

        $found = TRUE;
        $new_val = 0;

        foreach ($possibilities as $pos1) 
        {
            if (!in_array($pos1, $col_possibilities))
            {
                $found = FALSE;
                $new_val = $pos1;
            }
        }

        if (!$found) 
        {
            return $this->set_position($row, $col, $new_val);
        }
    }

    public function set_position($row, $col, $value)
    {
        $board = $this->board;
        $place = new Place($row, $col, $value);
        $this->changes++;
        $board[$row - 1][$col - 1] = $place;
        $this->set_board($board);

        return TRUE;
    }

    private function set_possibilities($row, $col, $possibilities)
    {
        $board = $this->board;
        $place = new Place($row, $col, 0, $possibilities);
        $board[$row - 1][$col - 1] = $place;
        $this->set_board($board);
        return TRUE;
    }

    private function set_cols($board)
    {
        foreach ($board as $row) 
        {
            foreach ($row as $place) 
            {
                $this->rows[$place->row][$place->col - 1] = $place;
            }
        }
    }

    private function set_rows($board)
    {
        foreach ($board as $row) 
        {
            foreach ($row as $place) 
            {
                $this->cols[$place->col][$place->row - 1] = $place;
            }
        }
    }

    private function set_blocks($board)
    {
        foreach ($board as $row) 
        {
            foreach ($row as $place) 
            {
                $this->blocks[$this->get_block($place->row, $place->col)][$this->get_block_position($place->row, $place->col)] = $place;
            }
        }
    }

    private function get_block($row, $col)
    {
        $num_blocks = $this->size;
        $block_divider = sqrt($num_blocks);
        $block_row = 0;
        $block_col = 0;

        $block_row = ceil($row/$block_divider);
        $block_col = ceil($col/$block_divider);

        $block = $block_col + (($block_row - 1) * $block_divider);

        return $block;
    }

    private function get_block_position($row, $col)
    {
        $block = $this->get_block($row, $col);

        $block_pos_row = $row % sqrt($this->size);

        if ($block_pos_row == 0) 
        {
            $block_pos_row = sqrt($this->size);
        }
        $block_pos_col = $col % sqrt($this->size);

        if ($block_pos_col == 0) 
        {
            $block_pos_col = sqrt($this->size);
        }

        $block = ((($block_pos_row - 1) * sqrt($this->size)) + $block_pos_col) - 1;

        return $block;
    }

    private function get_least_options()
    {
        $best_options = $this->size;
        $best_place = FALSE;

        foreach ($this->board as $row) 
        {
            foreach ($row as $place)
            {
                if ($place->value == 0) 
                {
                    $place_options = $this->get_possibilities($place->row, $place->col);

                    if (count($place_options) < $best_options) 
                    {
                        $best_options = count($place_options);
                        $best_place = $place;
                    }
                }
            }
        }

        return $best_place;
    }
}

/**
* 
*/
class Place
{
    public $row;
    public $col;
    public $block = 0;
    public $value;
    public $possibilities = array();

    function __construct($row, $col, $value = 0, $possibilities = array())
    {
        $this->row = $row;
        $this->col = $col;
        $this->value = $value;
        $this->possibilities = $possibilities;
    }

    public function set_possibilities($possibilities)
    {
        $this->possibilities = $possibilities;
    }
}

?>
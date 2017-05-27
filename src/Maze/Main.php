<?php

namespace Maze;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as T;
class Main extends PluginBase 
{
	
    public $prefs;
    public $wall_list = [];
    
    public function onEnable() 
    {
        @mkdir($this->getDataFolder());
       
	$this->prefs = new Config($this->getDataFolder() . "config.yml", CONFIG::YAML, array(
			"min_size_x" => 5,
			"max_size_x" => 99,
			"min_size_z" => 5,
			"max_size_z" => 99,
			"min_walls_height" => 3,
			"max_walls_height" => 10
	));
		
	$this->getLogger()->info("Maze has been enabled");
    }
    
    public function in_maze($row, $col, $size_x, $size_z) 
    {
        return $row > 0 and $row < $size_z - 1 and $col > 0 and $col < $size_x - 1; 
    }
    public function add_walls($row, $col, $size_x, $size_z)
    {
        $dir = [[0, 1], [1, 0], [0, -1], [-1, 0]];
        
        for($i=0;$i<count($dir);$i++){
            $wall_row = $row + $dir[$i][0];
            $wall_col = $col + $dir[$i][1];
            $cell_row = $wall_row + $dir[$i][0];
            $cell_col = $wall_col + $dir[$i][1];
        

            if(!($this->in_maze($wall_row, $wall_col, $size_x, $size_z)) or 
               !($this->in_maze($cell_row, $cell_col, $size_x, $size_z))){
                continue;
            }

            array_push($this->wall_list, [$wall_row, $wall_col, $cell_row, $cell_col]);
        }
    }
    public function generateMaze(CommandSender $sender, $size_x, $walls_height, $size_z, Block $block)
    {
        $maze = [];
        $this->wall_list = [];
        for($x=0;$x<$size_x;$x++){
             for($z=0;$z<$size_z;$z++){
                 $maze[$x][$z] = 1;
             }
        }
        $cell_row = 1;
        $cell_col = 1;
        $maze[$cell_row][$cell_col] = 0; 
        $this->add_walls($cell_row, $cell_col, $size_z, $size_x);
        
        while(!empty($this->wall_list)){
            
            $id = rand(0, count($this->wall_list)-1);
            
            $wall_row = $this->wall_list[$id][0];
            $wall_col = $this->wall_list[$id][1];
            $cell_row = $this->wall_list[$id][2];
            $cell_col = $this->wall_list[$id][3];
            
            unset($this->wall_list[$id]);
            $this->wall_list = array_values($this->wall_list);
        
            if($maze[$wall_row][$wall_col] != 1){
                continue;
            }
            
            if($maze[$cell_row][$cell_col] == 0){
                continue;
            }
        
            $maze[$wall_row][$wall_col] = 0;
            $maze[$cell_row][$cell_col] = 0;

            $this->add_walls($cell_row, $cell_col, $size_z, $size_x, $this->wall_list);
        }
        
        $this->getLogger()->notice("Generating maze with size -> x:$size_x/z:$size_z | height:$walls_height | wall_block : {$block->getName()}");
        
        $maze[1][0] = 0;
        $maze[$size_x-2][$size_z-1] = 0;
        for($z=0; $z<$size_z; $z++){
            $s = "";
            for($x=0; $x<$size_x; $x++){
                if($maze[$x][$z]==1){
                    for($y=0; $y<$walls_height; $y++){
                        $sender->getLevel()->setBlock(new Vector3($sender->getX() - $size_x/2 + $x, $sender->getY() + $y, $sender->getZ() - $size_z/2 + $z), $block);
                    }
                   $s .= T::YELLOW."#";
                } else {
                    if($x==1 && $z==0){
                        $s .= T::ITALIC.T::BOLD.T::GREEN."S".T::RESET;
                    } else if($x==$size_x-2 && $z==$size_z-1){
                        $s .= T::ITALIC.T::BOLD.T::RED."F".T::RESET;
                    } else {
                        $s .= T::AQUA.".";
                    }
                }
            
            }
            $this->getLogger()->info($s);
        }
    }
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) 
    {
        if ($cmd->getName() == "maze") {
            if(!($sender instanceof Player)){
                $sender->sendMessage(T::RED . "You must be in game to execute the command!");
                return false;
            }
            if(count($args) != 4){
                $sender->sendMessage(T::RED . "Usage : \n". T::AQUA . "/maze <size_x> <size_y> <wall_height> <block_id[:block_meta]>");
                return false;
            }
            if($args[0] < $this->prefs->get("min_size_x") or $args[0] > $this->prefs->get("max_size_x")){
                $sender->sendMessage(T::RED . "The sizeX of the maze must be {$this->prefs->get("min_size_x")} <= sizeX <= {$this->prefs->get("max_size_x")}");
                return false;
            }
            if($args[1] < $this->prefs->get("min_size_z") or $args[1] > $this->prefs->get("max_size_z")){
                $sender->sendMessage(T::RED . "The sizeZ of the maze must be {$this->prefs->get("min_size_z")} <= sizeZ <= {$this->prefs->get("max_size_z")}");
                return false;
            }
            if($args[2] < $this->prefs->get("min_walls_height") or $args[2] > $this->prefs->get("max_walls_height")){
                $sender->sendMessage(T::RED . "The height of the maze must be {$this->prefs->get("min_walls_height")} <= wallHeight <= {$this->prefs->get("max_walls_height")}");
                return false;
            }
            if($args[0]%2==0 or $args[1]%2==0){
                $sender->sendMessage(T::RED . "Both sizeX and sizeZ must be odd numbers");
                return false;
            }
            $data = explode(":", $args[3]);
            if(count($data)==2 && !empty($data[1])){
               $block = Block::get($data[0], $data[1]);
            } else {
               $block = Block::get($data[0], 0);
            }
            
            $this->generateMaze($sender, $args[0], $args[2], $args[1], $block);
            $sender->teleport(new Vector3($sender->getX(), $sender->getY() + 2*$args[2], $sender->getZ()));
            
            $sender->sendMessage(T::GREEN."Generating maze with size -> \nx:$args[0]/z:$args[1] | height:$args[2] | wall_block : {$block->getName()}");
            return true;
        }
    }
    
	
    public function onDisable() 
    {
	$this->getLogger()->info("Maze has been disabled");
    }
}

# Maze
A plugin for generating mazes created by Panda843

Usage : /maze <size_x> <size_z> <walls_height> <wall block id[:block meta]>

Conditions :

<size_x> and <size_z> must both be odd numbers and must be in range of min_size_x/max_size_x and min_size_z/max_size_z, a range which you can edit in the config.yml

<wall_height> must be in range of min_walls_height/max_walls_height, a range which you can edit in the config.yml

I think this goes without saying but the <wall block id> must be an id of a block, not item or something. Highly not recommended to choose ids of Saplings, Flowers, Water and Lava as that will not be a proper maze and it might cause heavy on your server so you have been warned. If you do not know ids of blocks and items, visit this link : http://minecraft-ids.grahamedgecombe.com/

It is highly recommended to generate the maze on a flat area or in the air but keep in mind that only the walls of the maze are generated so you will have to build the floor (here it is recommended to use WorldEdit for that when you create big mazes). 

# Maze
A plugin for generating mazes created by Panda843

Usage : /maze <size_x> <size_z> <walls_height> <wall block id[:block meta]>

Conditions :

<size_x> and <size_z> must be odd numbers and must be in range of min_size_x/max_size_x and min_size_z/max_size_z, a range which you can edit in the config.yml
<wall_height> must be in range of min_walls_height/max_walls_height, a range which you can edit in the config.yml

I think this goes without saying but the wall block ID must be an id of a block, not item or something. Highly not recommended to choose ids of Saplings and Flowers as that will not be a proper maze and it will HEAVILY lag your server. You have been warned

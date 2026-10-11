基于 ROS 导航框架( http://www.dengke.xyz/archives/2413 )，DKbot 导航整体设计如下图。在人机交互界面中发布导航目标位置，DKbot 执行全局、局部路径规划，规划结果通过控制指令发布给底层驱动，从而控制机器人运动。
里程计、激光数据、地图均已在前文实现，接下来主要配置 move_base 和 amcl。

<!--more-->

![dkbot7_01](http://www.dengke.xyz/usr/uploads/2019/12/4264048675.png)

# 第 1 章 安装

安装 ROS 导航框架，该框架提供了导航核心功能、基础路径规划算法的实现、amcl 定位、map_server 等工具：

```shell
sudo apt-get install ros-kinetic-navigation
```

# 第 2 章 move_base 配置

* 文件：

move_base.launch.xml

* 配置：

为 move_base 节点添加 5 个配置文件，参数文件具体内容为 2.1-2.5 所示。

```xml
<launch>
  <node pkg="move_base" type="move_base" respawn="false" name="move_base" output="screen" clear_params="true">
    <rosparam file="$(find dkbot_navigation)/params/local_costmap_params.yaml" command="load" ns="local_costmap"/>
    <rosparam file="$(find dkbot_navigation)/params/global_costmap_params.yaml" command="load" ns="global_costmap"/>
    <rosparam file="$(find dkbot_navigation)/params/dwa_local_planner_params.yaml" command="load" ns="DWAPlannerROS"/>
    <rosparam file="$(find dkbot_navigation)/params/move_base_params.yaml" command="load" />
    <rosparam file="$(find dkbot_navigation)/params/global_planner_params.yaml" command="load" ns="GlobalPlanner"/>
  </node>
</launch>
```

## 2.1 move_base 参数

* 文件：

move_base_params.yaml

* 各参数含义：

http://wiki.ros.org/move_base

* 配置：

配置 move_base 相关参数，其中设置使用 global_planner/GlobalPlanner 作全局规划，局部规划使用 dwa_local_planner/DWAPlannerROS。

```C++
base_global_planner: "global_planner/GlobalPlanner"
base_local_planner: "dwa_local_planner/DWAPlannerROS"
recovery_behaviors:
  - {name: conservative_reset, type: clear_costmap_recovery/ClearCostmapRecovery} 
  - {name: rotate_recovery, type: rotate_recovery/RotateRecovery} 
  - {name: aggressive_reset, type: clear_costmap_recovery/ClearCostmapRecovery}

# The rate in Hz at which to run the global planning loop. If the frequency is set to 0.0, the global planner will only run when a new goal is received or the local planner reports that its path is blocked.(double, default: 0.0)
planner_frequency: 2.0

# The rate in Hz at which to run the control loop and send velocity commands to the base.(double, default: 20.0)
controller_frequency: 5.0

# How long the planner will wait in seconds in an attempt to find a valid plan before space-clearing operations are performed.(double, default: 5.0)
planner_patience: 3.0

# How long the controller will wait in seconds without receiving a valid control before space-clearing operations are performed.(double, default: 15.0)
controller_patience: 2.0

# Whether or not to enable the move_base recovery behaviors to attempt to clear out space.(bool, default: true)
recovery_behavior_enabled: true

# Determines whether or not the robot will attempt an in-place rotation when attempting to clear out space. Note: This parameter is only used when the default recovery behaviors are in use, meaning the user has not set the recovery_behaviors parameter to anything custom.(bool, default: true)
clearing_rotation_allowed: true

# Determines whether or not to shutdown the costmaps of the node when move_base is in an inactive state (bool, default: false)
shutdown_costmaps: false

# How long in seconds to allow for oscillation before executing recovery behaviors. A value of 0.0 corresponds to an infinite timeout. (double, default: 0.0)
oscillation_timeout: 5.0

# How far in meters the robot must move to be considered not to be oscillating. Moving this far resets the timer counting up to the ~oscillation_timeout (double, default: 0.5)
oscillation_distance: 0.3

# How many times to allow for planning retries before executing recovery behaviors. A value of -1.0 corresponds to an infinite retries.(int32_t, default: -1)
#max_planning_retries: -1

#############################
# plug for recovery_behaviors
#############################
conservative_reset:
# The radius away from the robot in meters outside which obstacles will be removed from the costmaps when they are reverted to the static map.
    reset_distance: 1.0

aggressive_reset:
# The radius away from the robot in meters outside which obstacles will be removed from the costmaps when they are reverted to the static map.
    reset_distance: 0.0
```

## 2.2 全局代价地图

* 文件：

global_costmap_params.yaml

* 各参数含义：

http://wiki.ros.org/navigation/Tutorials/RobotSetup

* 配置：

```C++
# ns : global_costmap

# The global frame for the costmap to operate in. (string, default: "/map")
global_frame: map

# The name of the frame for the base link of the robot.(string, default: "base_link")
robot_base_frame: base_link

# distance a circular robot should be clear of the obstacle (kobuki: 0.18)
# robot_radius: 0.20  
footprint: [[0.175, 0.15], [0.175, -0.15], [-0.175, -0.15], [-0.175, 0.15]]  # if the robot is not circular

# used for obstacle_layer, 3D->voxel 2D->other
map_type: costmap

# Specifies the delay in transform (tf) data that is tolerable in seconds. This parameter serves as a safeguard to losing a link in the tf tree while still allowing an amount of latency the user is comfortable with to exist in the system. For example, a transform being 0.2 seconds out-of-date may be tolerable, but a transform being 8 seconds out of date is not. If the tf transform between the coordinate frames specified by the global_frame and robot_base_frame parameters is transform_tolerance seconds older than ros::Time::now(), then the navigation stack will stop the robot.(double, default: 0.2)
transform_tolerance: 0.5

# The frequency in Hz for the map to be updated.(double, default: 5.0)
update_frequency: 5.0

# The frequency in Hz for the map to be publish display information.(double, default: 0.0)
publish_frequency: 0.5

# Whether or not to use a rolling window version of the costmap. If the static_map parameter is set to true, this parameter must be set to false. (bool, default: false)
rolling_window: false
static_map: true

# If true the full costmap is published to "~<name>/grid" every update. If false only the part of the costmap that has changed is published on the "~<name>/grid_updates" topic. (bool, default: false)
always_send_full_costmap: false

# Sequence of plugin specifications, one per layer. Each specification is a dictionary with name and type fields. The name is used to define the parameter namespace for the plugin.
plugins:
  - {name: static_layer,            type: "costmap_2d::StaticLayer"}
  - {name: obstacle_layer,          type: "costmap_2d::ObstacleLayer"}
  - {name: inflation_layer,         type: "costmap_2d::InflationLayer"}

# The width of the map in meters.(int, default: 10)
width: 10

# The height of the map in meters.(int, default: 10)
height: 10

# The resolution of the map in meters/cell. (double, default: 0.05)
resolution: 0.05

# The x origin of the map in the global frame in meters. (double, default: 0.0)
origin_x: 0.0

# The y origin of the map in the global frame in meters. (double, default: 0.0)
origin_y: 0.0

#############################
#
#        static_layer
#
#############################
static_layer:
# The value for which a cost should be considered unknown when reading in a map from the map server. If the costmap is not tracking unknown space, costs of this value will be considered occupied. A value of zero also results in this parameter being unused. (int, default: -1)
  unknown_cost_value: -1

# The threshold value at which to consider a cost lethal when reading in a map from the map server.(int, default: 100)
  lethal_cost_threshold: 100

# The topic that the costmap subscribes to for the static map. This parameter is useful when you have multiple costmap instances within a single node that you want to use different static maps. (string, default: "map")
  map_topic: map

# Only subscribe to the first message on the map topic, ignoring all subsequent messages (bool, default: false)
  first_map_only: false

# In addition to map_topic, also subscribe to map_topic + "_updates" (bool, default: false)
  subscribe_to_updates: false

# If true, unknown values in the map messages are translated directly to the layer. Otherwise, unknown values in the map message are translated as FREE_SPACE in the layer.  (bool, default: true)
  track_unknown_space: true

# Only matters if the static layer is not the bottom layer. If true, only the maximum value will be written to the master costmap. (bool, default: false)
  use_maximum: false

# If true, translates all map message values to NO_INFORMATION/FREE_SPACE/LETHAL_OBSTACLE (three values). If false, a full spectrum of intermediate values is possible. (bool, default: true)
  trinary_costmap: true

#############################
#
#        inflation_layer
#
#############################
inflation_layer:
# The radius in meters to which the map inflates obstacle cost values. (double, default: 0.55)
  inflation_radius: 0.30

# A scaling factor to apply to cost values during inflation. The cost function is computed as follows for all cells in the costmap further than the inscribed radius distance and closer than the inflation radius distance away from an actual obstacle: exp(-1.0 * cost_scaling_factor * (distance_from_obstacle - inscribed_radius)) * (costmap_2d::INSCRIBED_INFLATED_OBSTACLE - 1), where costmap_2d::INSCRIBED_INFLATED_OBSTACLE is currently 254. NOTE: since the cost_scaling_factor is multiplied by a negative in the formula, increasing the factor will decrease the resulting cost values. (double, default: 10.0)
  cost_scaling_factor: 10.0

#############################
#
#      obstacle_layer
#
#############################
obstacle_layer:
# The maximum height of any obstacle to be inserted into the costmap in meters. This parameter should be set to be slightly higher than the height of your robot. (double, default: 2.0)
  max_obstacle_height: 2.0
# The default maximum distance from the robot at which an obstacle will be inserted into the cost map in meters. This can be over-ridden on a per-sensor basis. (double, default: 2.5)
  obstacle_range: 3.0
# The default range in meters at which to raytrace out obstacles from the map using sensor data. This can be over-ridden on a per-sensor basis. (double, default: 3.0)
  raytrace_range: 4.0
# If false, each pixel has one of 2 states: lethal obstacle or free. If true, each pixel has one of 3 states: lethal obstacle, free, or unknown.  (bool, default: false)
  track_unknown_space: false
# If true, the robot footprint will clear (mark as free) the space in which it travels. (bool, default: true)
  footprint_clearing_enabled: true
# A list of observation source names separated by spaces. This defines each of the <source_name> namespaces defined below. (string, default: "")
  observation_sources: scan
  
  scan:
# The topic on which sensor data comes in for this source. Defaults to the name of the source. (string, default: source_name)
    topic: scan
# The frame of the origin of the sensor. Leave empty to attempt to read the frame from sensor data. The frame can be read from both sensor_msgs/LaserScan, sensor_msgs/PointCloud, and sensor_msgs/PointCloud2 messages. (string, default: "")
    sensor_frame: ""
# How long to keep each sensor reading in seconds. A value of 0.0 will only keep the most recent reading. (double, default: 0.0)
    observation_persistence: 0.0
# How often to expect a reading from a sensor in seconds. A value of 0.0 will allow infinite time between readings. This parameter is used as a failsafe to keep the navigation stack from commanding the robot when a sensor has failed. It should be set to a value that is slightly more permissive than the actual rate of the sensor. For example, if we expect a scan from a laser every 0.05 seconds we might set this parameter to be 0.1 seconds to give a generous buffer and account for some amount of system latency. (double, default: 0.0)
    expected_update_rate: 0.0
# The data type associated with the topic, right now only "PointCloud", "PointCloud2", and "LaserScan" are supported. (string, default: "PointCloud")
    data_type: LaserScan
# Whether or not this observation should be used to clear out freespace.  (bool, default: false)
    clearing: true
# Whether or not this observation should be used to mark obstacles. (bool, default: true)
    marking: true
# The maximum height in meters of a sensor reading considered valid. This is usually set to be slightly higher than the height of the robot. Setting this parameter to a value greater than the global max_obstacle_height parameter has no effect. Setting this parameter to a value less than the global max_obstacle_height will filter out points from this sensor above that height.(double, default: 2.0)
    max_obstacle_height: 2.0
# The minimum height in meters of a sensor reading considered valid. This is usually set to be at ground height, but can be set higher or lower based on the noise model of your sensor. (double, default: 0.0)
    min_obstacle_height: 0.0
# The maximum range in meters at which to insert obstacles into the costmap using sensor data.  (double, default: 2.5)
    obstacle_range: 3.0
# The maximum range in meters at which to raytrace out obstacles from the map using sensor data.  (double, default: 3.0)
    raytrace_range: 4.0
# Allows for Inf values in "LaserScan" observation messages. The Inf values are converted to the laser maximum range.  (bool, default: false)
    inf_is_valid: true
```

## 2.3 全局规划

* 文件：

global_planner_params.yaml

* 配置：

设置 GlobalPlanner 相关参数。

```C++
# ns : GlobalPlanner

# Specifies whether or not to allow the planner to create plans that traverse unknown space. NOTE: if you are using a layered costmap_2d costmap with a voxel or obstacle layer, you must also set the track_unknown_space param for that layer to be true, or it will convert all your unknown space to free space (which planner will then happily go right through). (bool, default: true)
allow_unknown: true

# A tolerance on the goal point for the planner. The planner will attempt to create a plan that is as close to the specified goal as possible but no further than default_tolerance away. (double, default: 0.0)
default_tolerance: 0.0

# Specifies whether or not to visualize the potential area computed via a PointCloud2. (bool, default: false)
visualize_potential: false

# If true, use dijkstra's algorithm. Otherwise, A*. (bool, default: true)
use_dijkstra: true

# If true, use the quadratic approximation of the potential. Otherwise, use a simpler calculation. (bool, default: true)
use_quadratic: true

# If true, create a path that follows the grid boundaries. Otherwise, use a gradient descent method. (bool, default: false)
use_grid_path: false

# If for some reason, you want global_planner to exactly mirror the behavior of navfn, set this to true (and use the defaults for the other boolean parameters) (bool, default: false)
old_navfn_behavior: false
```

## 2.4 局部代价地图

* 文件：

local_costmap_params.yaml

* 各参数含义：

http://wiki.ros.org/navigation/Tutorials/RobotSetup

* 配置：

```C++
# ns : local_costmap

# The global frame for the costmap to operate in. (string, default: "/map")
global_frame: odom

# The name of the frame for the base link of the robot.(string, default: "base_link")
robot_base_frame: base_link

# distance a circular robot should be clear of the obstacle (kobuki: 0.18)
#robot_radius: 0.20  
footprint: [[0.18, 0.15], [0.18, -0.15], [-0.18, -0.15],[-0.18, 0.15]]  # if the robot is not circular

# used for obstacle_layer, 3D->voxel 2D->other
map_type: costmap

# Specifies the delay in transform (tf) data that is tolerable in seconds. This parameter serves as a safeguard to losing a link in the tf tree while still allowing an amount of latency the user is comfortable with to exist in the system. For example, a transform being 0.2 seconds out-of-date may be tolerable, but a transform being 8 seconds out of date is not. If the tf transform between the coordinate frames specified by the global_frame and robot_base_frame parameters is transform_tolerance seconds older than ros::Time::now(), then the navigation stack will stop the robot.(double, default: 0.2)
transform_tolerance: 0.5

# The frequency in Hz for the map to be updated.(double, default: 5.0)
update_frequency: 5.0

# The frequency in Hz for the map to be publish display information.(double, default: 0.0)
publish_frequency: 0.5

# Whether or not to use a rolling window version of the costmap. If the static_map parameter is set to true, this parameter must be set to false. (bool, default: false)
rolling_window: true
static_map: false

# If true the full costmap is published to "~<name>/grid" every update. If false only the part of the costmap that has changed is published on the "~<name>/grid_updates" topic. (bool, default: false)
always_send_full_costmap: false

# Sequence of plugin specifications, one per layer. Each specification is a dictionary with name and type fields. The name is used to define the parameter namespace for the plugin.
plugins:
  - {name: obstacle_layer,          type: "costmap_2d::ObstacleLayer"}
  - {name: inflation_layer,         type: "costmap_2d::InflationLayer"}

# The width of the map in meters.(int, default: 10)
width: 5.0

# The height of the map in meters.(int, default: 10)
height: 5.0

# The resolution of the map in meters/cell. (double, default: 0.05)
resolution: 0.05

# The x origin of the map in the global frame in meters. (double, default: 0.0)
origin_x: 0.0

# The y origin of the map in the global frame in meters. (double, default: 0.0)
origin_y: 0.0

#############################
#
#        inflation_layer
#
#############################
inflation_layer:
# The radius in meters to which the map inflates obstacle cost values. (double, default: 0.55)
  inflation_radius: 0.20

# A scaling factor to apply to cost values during inflation. The cost function is computed as follows for all cells in the costmap further than the inscribed radius distance and closer than the inflation radius distance away from an actual obstacle: exp(-1.0 * cost_scaling_factor * (distance_from_obstacle - inscribed_radius)) * (costmap_2d::INSCRIBED_INFLATED_OBSTACLE - 1), where costmap_2d::INSCRIBED_INFLATED_OBSTACLE is currently 254. NOTE: since the cost_scaling_factor is multiplied by a negative in the formula, increasing the factor will decrease the resulting cost values. (double, default: 10.0)
  cost_scaling_factor: 10.0

#############################
#
#      obstacle_layer
#
#############################
obstacle_layer:
# The maximum height of any obstacle to be inserted into the costmap in meters. This parameter should be set to be slightly higher than the height of your robot. (double, default: 2.0)
  max_obstacle_height: 2.0
# The default maximum distance from the robot at which an obstacle will be inserted into the cost map in meters. This can be over-ridden on a per-sensor basis. (double, default: 2.5)
  obstacle_range: 3.0
# The default range in meters at which to raytrace out obstacles from the map using sensor data. This can be over-ridden on a per-sensor basis. (double, default: 3.0)
  raytrace_range: 4.0
# If false, each pixel has one of 2 states: lethal obstacle or free. If true, each pixel has one of 3 states: lethal obstacle, free, or unknown.  (bool, default: false)
  track_unknown_space: false
# If true, the robot footprint will clear (mark as free) the space in which it travels. (bool, default: true)
  footprint_clearing_enabled: true
# A list of observation source names separated by spaces. This defines each of the <source_name> namespaces defined below. (string, default: "")
  observation_sources: scan
  
  scan:
# The topic on which sensor data comes in for this source. Defaults to the name of the source. (string, default: source_name)
    topic: scan
# The frame of the origin of the sensor. Leave empty to attempt to read the frame from sensor data. The frame can be read from both sensor_msgs/LaserScan, sensor_msgs/PointCloud, and sensor_msgs/PointCloud2 messages. (string, default: "")
    sensor_frame: ""
# How long to keep each sensor reading in seconds. A value of 0.0 will only keep the most recent reading. (double, default: 0.0)
    observation_persistence: 0.0
# How often to expect a reading from a sensor in seconds. A value of 0.0 will allow infinite time between readings. This parameter is used as a failsafe to keep the navigation stack from commanding the robot when a sensor has failed. It should be set to a value that is slightly more permissive than the actual rate of the sensor. For example, if we expect a scan from a laser every 0.05 seconds we might set this parameter to be 0.1 seconds to give a generous buffer and account for some amount of system latency. (double, default: 0.0)
    expected_update_rate: 0.0
# The data type associated with the topic, right now only "PointCloud", "PointCloud2", and "LaserScan" are supported. (string, default: "PointCloud")
    data_type: LaserScan
# Whether or not this observation should be used to clear out freespace.  (bool, default: false)
    clearing: true
# Whether or not this observation should be used to mark obstacles. (bool, default: true)
    marking: true
# The maximum height in meters of a sensor reading considered valid. This is usually set to be slightly higher than the height of the robot. Setting this parameter to a value greater than the global max_obstacle_height parameter has no effect. Setting this parameter to a value less than the global max_obstacle_height will filter out points from this sensor above that height.(double, default: 2.0)
    max_obstacle_height: 2.0
# The minimum height in meters of a sensor reading considered valid. This is usually set to be at ground height, but can be set higher or lower based on the noise model of your sensor. (double, default: 0.0)
    min_obstacle_height: 0.0
# The maximum range in meters at which to insert obstacles into the costmap using sensor data.  (double, default: 2.5)
    obstacle_range: 3.0
# The maximum range in meters at which to raytrace out obstacles from the map using sensor data.  (double, default: 3.0)
    raytrace_range: 4.0
# Allows for Inf values in "LaserScan" observation messages. The Inf values are converted to the laser maximum range.  (bool, default: false)
    inf_is_valid: true
```

## 2.5 局部规划

* 文件：

local_planner_params.yaml

* 各参数含义：

http://wiki.ros.org/base_local_planner

* 配置：

设置使用 Trajectory 算法的参数。

```C++
# ns : DWAPlannerROS

##################################
#
#  Robot Configuration Parameters
#
##################################

# The x acceleration limit of the robot in meters/sec^2 (double, default: 2.5)
acc_lim_x: 3.0

# The y acceleration limit of the robot in meters/sec^2 (double, default: 2.5)
acc_lim_y: 0.0

# The rotational acceleration limit of the robot in radians/sec^2 (double, default: 3.2)
acc_lim_theta: 10.0

# The absolute value of the maximum translational velocity for the robot in m/s (double, default: 0.55)
max_trans_vel: 0.3

# The absolute value of the minimum translational velocity for the robot in m/s (double, default: 0.1)
min_trans_vel: 0.1

# The maximum x velocity for the robot in m/s. (double, default: 0.55)
max_vel_x: 0.3

# The minimum x velocity for the robot in m/s, negative for backwards motion. (double, default: 0.0)
min_vel_x: -0.3

# The maximum y velocity for the robot in m/s (double, default: 0.1)
max_vel_y: 0.0

# The minimum y velocity for the robot in m/s (double, default: -0.1)
min_vel_y: 0.0

# The absolute value of the maximum rotational velocity for the robot in rad/s (double, default: 1.0)
max_rot_vel: 0.4

# The absolute value of the minimum rotational velocity for the robot in rad/s (double, default: 0.4)
min_rot_vel: 0.1

rot_stopped_vel: 0.1
trans_stopped_vel: 0.1
##################################
#
#  Goal Tolerance Parameters
#
##################################

# The tolerance in radians for the controller in yaw/rotation when achieving its goal (double, default: 0.05)
yaw_goal_tolerance: 1.0

# The tolerance in meters for the controller in the x & y distance when achieving a goal (double, default: 0.10)
xy_goal_tolerance: 0.2

# If goal tolerance is latched, if the robot ever reaches the goal xy location it will simply rotate in place, even if it ends up outside the goal tolerance while it is doing so. (bool, default: false)
latch_xy_goal_tolerance: false

##################################
#
#  Forward Simulation Parameters
#
##################################

# The amount of time to forward-simulate trajectories in seconds (double, default: 1.7)
sim_time: 1.0

# The step size, in meters, to take between points on a given trajectory (double, default: 0.025)
sim_granularity: 0.03
angular_sim_granularity: 0.1

# The number of samples to use when exploring the x velocity space (integer, default: 3)
vx_samples: 6

# The number of samples to use when exploring the y velocity space (integer, default: 10)
vy_samples: 1

# The number of samples to use when exploring the theta velocity space (integer, default: 20)
vtheta_samples: 5

# The frequency at which this controller will be called in Hz. Uses searchParam to read the parameter from parent namespaces if not set in the namespace of the controller. For use with move_base, this means that you only need to set its "controller_frequency" parameter and can safely leave this one unset. (double, default: 20.0)
#controller_frequency: 20

##################################
#
#   Trajectory Scoring Parameters
#
# cost =  pdist_scale * (distance to path from the endpoint of the trajectory in map cells or meters depending on the meter_scoring parameter) 
#  +      gdist_scale * (distance to local goal from the endpoint of the trajectory in map cells or meters depending on the meter_scoring parameter) 
#  +      occdist_scale * (maximum obstacle cost along the trajectory in obstacle cost (0-254))
#
##################################

# The weighting for how much the controller should stay close to the path it was given (double, default: 32.0)
path_distance_bias: 64.0

# The weighting for how much the controller should attempt to reach its local goal, also controls speed (double, default: 24.0)
goal_distance_bias: 24.0

# The weighting for how much the controller should attempt to avoid obstacles (double, default: 0.01)
occdist_scale: 0.5

# The distance from the center point of the robot to place an additional scoring point, in meters (double, default: 0.325)
forward_point_distance: 0.2

# The amount of time that the robot must stop before a collision in order for a trajectory to be considered valid in seconds (double, default: 0.2)
stop_time_buffer: 0.2

# The absolute value of the velocity at which to start scaling the robot's footprint, in m/s (double, default: 0.25)
scaling_speed: 0.25

# The maximum factor to scale the robot's footprint by (double, default: 0.2)
max_scaling_factor: 0.2

#################################
#
# RotateFirtCostFunction
#
#################################

# mininum angle robot to path and maximux distance from robot to path begin to make the function work
angle_threshold: 2.0
dist_threshold: 0.2

# maximum x direction velocity, this function mainly for rotate robot to path direction, so we have to limit x velocity
max_xv: 0.07

# cost factor for score, the higher it is, the bigger effect it has
cost_factor: 10.0

# little bias to defend zero overflow
thetav_bias: 0.2

##################################
#
#  Oscillation Prevention Parameters
#
##################################

# How far the robot must travel in meters before oscillation flags are reset (double, default: 0.05)
oscillation_reset_dist: 0.05


##################################
#
#      Global Plan Parameters
#
##################################

# Defines whether or not to eat up the plan as the robot moves along the path. If set to true, points will fall off the end of the plan once the robot moves 1 meter past them. (bool, default: true)
prune_plan: true

##################################
#
# Rivz Display Parameters for Debug
#
##################################

# publish dwa cost grid point cloud
publish_cost_grid_pc: true

# publish dwa trajectory point cloud
publish_traj_pc: true
```

# 第 3 章 amcl 配置

* 文件：

amcl.launch.xml

* 各参数含义：

http://wiki.ros.org/amcl

* 配置：

```xml
<launch>

  <!--arg name="use_map_topic" default="false"/>
  <arg name="scan_topic" default="scan"/-->

  <node pkg="amcl" type="amcl" name="amcl" clear_params="true">
    <param name="use_map_topic" value="false"/>
    <!-- Publish scans from best pose at a max of 10 Hz -->
    <param name="odom_model_type" value="diff"/>
    <param name="odom_alpha5" value="0.1"/>
    <param name="gui_publish_rate" value="10.0"/>
    <param name="laser_max_beams" value="60"/>
    <param name="laser_max_range" value="12.0"/>
    <param name="min_particles" value="500"/>
    <param name="max_particles" value="2000"/>
    <param name="kld_err" value="0.05"/>
    <param name="kld_z" value="0.99"/>
    <param name="odom_alpha1" value="0.2"/>
    <param name="odom_alpha2" value="0.2"/>
    <!-- translation std dev, m -->
    <param name="odom_alpha3" value="0.2"/>
    <param name="odom_alpha4" value="0.2"/>
    <param name="laser_z_hit" value="0.5"/>
    <param name="laser_z_short" value="0.05"/>
    <param name="laser_z_max" value="0.05"/>
    <param name="laser_z_rand" value="0.5"/>
    <param name="laser_sigma_hit" value="0.2"/>
    <param name="laser_lambda_short" value="0.1"/>
    <param name="laser_model_type" value="likelihood_field"/>
    <!-- <param name="laser_model_type" value="beam"/> -->
    <param name="laser_likelihood_max_dist" value="2.0"/>
    <param name="update_min_d" value="0.25"/>
    <param name="update_min_a" value="0.2"/>
    <param name="odom_frame_id" value="odom"/>
    <param name="resample_interval" value="1"/>
    <!-- Increase tolerance because the computer can get quite busy -->
    <param name="transform_tolerance" value="1.0"/>
    <param name="recovery_alpha_slow" value="0.0"/>
    <param name="recovery_alpha_fast" value="0.0"/>
    <!--remap from="scan" to="$(arg scan_topic)"/-->
  </node>
</launch>
```

# 第 4 章 launch 文件

* 文件：

navigation.launch

* 配置：

添加雷达驱动，map_server，amcl 和 move_base 的配置文件。

```xml
<launch>
  <include file="$(find dkbot_lidar_gmapping)/launch/lidar_laser.launch" />

  <arg name="map_file" default="$(find dkbot_lidar_gmapping)/maps/dkbot_map.yaml"/>
  <node pkg="map_server" name="map_server"  type="map_server" args="$(arg map_file)" />

  <include file="$(find dkbot_navigation)/include/amcl.launch.xml" />
  <include file="$(find dkbot_navigation)/include/move_base.launch.xml" />
</launch>
```

# 第 5 章 测试

（1）DKbot 端：

* 启动底盘驱动：

```shell
roslaunch ros_arduino_python arduino.launch
```

* 运行导航启动文件：

```shell
roslaunch dkbot_lidar_gmapping lidar_slam.launch
```

（2）虚拟机端：

* 启动 rviz 工具，并加载 dkbot_navigation/rviz/view_navigation.rviz 文件：

```shell
rosrun rviz rviz
```

* 使用“2D Nav Estmate”校准 DKbot 起始位置，使用“2D Nav Goal”设置目标位置与方向。测试全局、局部规划，以及动态避障过程，如下图所示。

![dkbot7_02](http://www.dengke.xyz/usr/uploads/2019/12/2723209352.png)

* DKbot 导航中，节点状态图如下：

![dkbot7_03](http://www.dengke.xyz/usr/uploads/2019/12/3026435111.png)

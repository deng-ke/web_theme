gmapping 算法基于 Rao-Blackwellized 粒子滤波算法，该算法来自于 OpenSLAM 开源社区：https://openslam-org.github.io/gmapping 。
ROS 中 gmapping 功能包集成了该算法，DKbot 使用该包订阅激光雷达信息、里程计信息，并配置相关参数，创建输出基于概率的二维栅格地图，实现框架如下图所示。

<!--more-->

![dkbot6_01](http://www.dengke.xyz/usr/uploads/2019/12/1366027877.png)

# 第 1 章 创建 dkbot_lidar_gmapping 包

dkbot_lidar_gmapping 功能包用于实现 DKbot 基于 RPLIDAR A1 和 gmapping 算法的地图构建功能。

代码：https://github.com/deng-ke/DKbot/tree/master/dkbot_mapping/dkbot_lidar_gmapping

## 1.1 安装与创建

* 安装对应 ROS 版本的 gmapping 功能包：

```shell
sudo apt-get install ros-kinetic-gmapping
```

* 创建地图构建功能包 dkbot_lidar_gmapping。（参考：http://www.dengke.xyz/archives/17/#menu_index_19）

## 1.2 激光雷达坐标转换

激光雷达位置通常不在小车的中心，即雷达坐标系与 DKbot 基坐标系不一致，且雷达位置与 DKbot 相对静止。因此可利用 TF 静态坐标转换工具实现雷达坐标系、DKbot 基坐标系之间的映射。

在 rplidar_ros/launch/rplidar.launch 基础上完成上述工作，添加 laser.launch，根据激光雷达和底盘的偏移位置设置 args 参数：

```xml
<launch>
  <include file="$(find rikirobot)/launch/lidar/$(env RIKILIDAR).launch" />
  <node pkg="tf" type="static_transform_publisher" name="base_link_to_laser" args="0.04 0 0.18 0 0 0  /base_link /laser  100"/>
</launch>
```

## 1.3 gmapping 参数设置

创建 dkbot_lidar_gmapping/param/slam_gmapping.xml 配置文件，配置参数如下：

```xml
<launch>
  <node pkg="gmapping" type="slam_gmapping" name="slam_gmapping" output="screen">
    <param name="base_frame" value="/base_link" />
    <param name="odom_frame" value="/odom" />
    <param name="map_update_interval" value="15.0"/>
    <param name="maxUrange" value="5.0"/>
    <param name="minRange" value="-0.5"/>
    <param name="sigma" value="0.05"/>
    <param name="kernelSize" value="1"/>
    <param name="lstep" value="0.05"/>
    <param name="astep" value="0.05"/>
    <param name="iterations" value="5"/>
    <param name="lsigma" value="0.075"/>
    <param name="ogain" value="3.0"/>
    <param name="lskip" value="0"/>
    <param name="minimumScore" value="100"/>
    <param name="srr" value="0.01"/>
    <param name="srt" value="0.02"/>
    <param name="str" value="0.01"/>
    <param name="stt" value="0.02"/>
    <param name="linearUpdate" value="0.7"/>
    <param name="angularUpdate" value="0.7"/>
    <param name="temporalUpdate" value="-0.5"/>
    <param name="resampleThreshold" value="0.5"/>
    <param name="particles" value="50"/>
    <param name="xmin" value="-50.0"/>
    <param name="ymin" value="-50.0"/>
    <param name="xmax" value="50.0"/>
    <param name="ymax" value="50.0"/>
    <param name="delta" value="0.05"/>
    <param name="llsamplerange" value="0.05"/>
    <param name="llsamplestep" value="0.05"/>
    <param name="lasamplerange" value="0.005"/>
    <param name="lasamplestep" value="0.005"/>
    <param name="transform_publish_period" value="0.1"/>
  </node>
</launch>
```

## 1.4 launch 文件及 rviz 设置

launch 文件调用 lidar_laser.launch 和 slam_gmapping.xml：

```xml
<launch>
  <include file="$(find dkbot_lidar_gmapping)/launch/lidar_laser.launch" />
  <include file="$(find dkbot_lidar_gmapping)/param/slam_gmapping.xml" />
</launch>
```

rviz 文件在虚拟机中运行，以可视化界面显示地图构建过程，主要设置各类数据对应的话题。

## 1.5 地图文件目录

创建 dkbot_lidar_gmapping/maps 目录，用于存放地图文件，创建 dkbot_lidar_gmapping/maps/map.sh 脚本，添加如下命令调用 map_server 保存地图：

```shell
rosrun map_server map_saver -f dkbot_map
```

# 第 2 章 测试

## 2.1 构建地图

（1）DKbot 端：

* 启动底盘驱动：

```shell
roslaunch ros_arduino_python arduino.launch
```

* 运行 launch 文件，启动激光雷达、gmapping 节点：

```shell
roslaunch dkbot_lidar_gmapping lidar_slam.launch
```

（2）虚拟机端：

* 启动 rviz 工具，并加载 dkbot_lidar_gmapping/rviz/slam.rviz 文件：

```shell
rosrun rviz rviz
```

* 启动键盘控制程序：

```shell
rosrun teleop_twist_keyboard teleop_twist_keyboard.py
```

* 使用键盘控制机器人移动，创建室内地图。

![dkbot6_02](http://www.dengke.xyz/usr/uploads/2019/12/3246931863.png)

## 2.2 保存地图

保存地图：

```shell
roscd dkbot_lidar_gmapping/maps
./map.sh
```

第一次执行 map.sh 脚本前，需赋予其可执行权限：

```shell
chmod +x map.sh
```

![dkbot6_03](http://www.dengke.xyz/usr/uploads/2019/12/1555248245.png)

RPLIDAR A1 激光雷达，是 DKbot 实现 SLAM、导航功能的核心传感器，其主要参数如下：

* 最大测距范围：12m
* 扫描角度：360 度
* 扫描频率：2-10Hz 可调
* 测量频率：8000Hz

<!--more-->

通信协议、用户手册见：http://www.slamtec.com/cn/Support#rplidar-a1。

![dkbot5_01](http://www.dengke.xyz/usr/uploads/2019/12/2320671531.jpg)

# 第 1 章 坐标系

RPLIDAR 顺时针旋转，SDK 数据输出是带距离和角度信息的左手系数据，驱动包(rplidar_ros)已经将其转化为右手坐标系数据输出。A1 雷达数据坐标系如下图，尖的一端为 X 轴，朝向机器人正前方。

![dkbot5_02](http://www.dengke.xyz/usr/uploads/2019/12/2896890209.png)

# 第 2 章 驱动安装

rplidar_ros 包：http://wiki.ros.org/rplidar

rplidar_ros 包中节点为 rplidarNode，该节点使用 RPLIDAR 的 SDK 读取激光扫描数据，并转换为 ROS 中消息格式为 sensor_msgs/LaserScan 的消息。该包提供了 1 个主题，2 个服务和 5 个参数，如下图所示。

![dkbot5_03](http://www.dengke.xyz/usr/uploads/2019/12/742267280.png)

* 安装：

```shell
cd ~/catkin_ws/src/dkbot_mapping
git clone https://github.com/robopeak/rplidar_ros.git
```

* 编译：

```shell
cd ~/catkin_ws
catkin_make --pkg rplidar_ros
```

# 第 3 章 测试

## 3.1 udev 设置

编辑 rules 文件：

```shell
cd /etc/udev/rules.d
sudo nano dkbot.rules
```

与 Arduino 相同，给 rplidar 设置串口别名，并赋予权限 666：

```xml
KERNEL=="ttyUSB?", SUBSYSTEM=="tty", MODE="0666" SYMLINK+="dkbotlidar"
```

## 3.2 查看扫描数据

（1）修改 launch 文件配置，将参数 serial_port 修改为 3.1 中配置的别名，其他参数根据实际情况而定，一般不用修改：

```shell
roscd rplidar_ros/launch
nano rplidar.launch
```

![dkbot5_04](http://www.dengke.xyz/usr/uploads/2019/12/4270550377.png)

（2）在 DKbot 端运行：

```shell
roslaunch rplidar_ros rplidar.launch
```

（3）虚拟机端打开 rviz，并打开 rplidar_ros/rviz 中的 rplidar.rviz 文件，如图显示出红色边界为激光雷达扫描到的障碍物：

```shell
rosrun rviz rviz
```

![dkbot5_05](http://www.dengke.xyz/usr/uploads/2019/12/2815636411.jpg)

（4）在 DKbot 中查看/scan 话题中的消息：

```shell
rostopic echo /scan
```

![dkbot5_06](http://www.dengke.xyz/usr/uploads/2019/12/3048766907.png)

![dkbot5_07](http://www.dengke.xyz/usr/uploads/2019/12/2218615381.png)

![dkbot5_08](http://www.dengke.xyz/usr/uploads/2019/12/2837117478.png)

对比官网给出的输出数据格式，便可理解上述/scan 话题中的消息

![dkbot5_09](http://www.dengke.xyz/usr/uploads/2019/12/1511164795.png)

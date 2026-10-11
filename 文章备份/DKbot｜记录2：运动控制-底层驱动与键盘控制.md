首先为DKbot建立两驱差速运动模型，包括速度分解和航迹推演。在此模型基础上，使用ros_arduino_bridge框架和Arduino实现DKbot的底层驱动，并实现键盘遥控DKbot运动。

<!--more-->

# 第1章 运动模型

DKbot采用两驱差速底盘，如果两个驱动的速度不同，就会使得底盘朝着速度慢的一边转向；如果两个驱动的速度相同，则地盘直线前进。

在空间直角坐标系中，单个驱动轮的速度分解如下图所示，$ x $轴具有分速度，$ y $、$ z $轴速度均为0。

![dkbot2_01](http://www.dengke.xyz/usr/uploads/2019/11/2495573553.jpg)

对于双驱动，右侧驱动轮速度大于左侧驱动轮速度，机器人便向左转弯，并且具有$ x $、$ y $轴的分速度。

![dkbot2_02](http://www.dengke.xyz/usr/uploads/2019/11/1367901606.jpg)

基于此原理，机器人底层驱动需要解决两个问题：

* 速度分解：在ROS中给定机器人线速度和角速度，如何解算为两个驱动轮的控制参数。

* 航迹推演：根据两个驱动轮速度值，如何解算出机器人的线速度和角速度，进而推算出里程数据。

## 1.1 正解--速度分解

上述从机器人线、角速度分解为驱动轮速度的过程，即为正解，用于在ROS中控制机器人运动。

机器人的速度是指两个相邻的控制时刻之间的速度。由于ROS本身是依赖于高频率的发送控制命令来精确控制机器人的，常用的控制频率>10Hz，因此机器人在相邻时刻走过的弧度可用两个位置之间的直线来代替。基于上述假设，引入机器人坐标系。

![dkbot02_03](http://www.dengke.xyz/usr/uploads/2019/11/3031280273.jpg)

如上图所示，机器人坐标系的移动速度为$ V $，时间间隔为$ \Delta t $，两个驱动轮之间的距离为$ L $，分解后的左轮线速度为$ V_L $，右轮线速度为$ V_R $，基于上述假设可得：

$$ V_L=(V* \Delta t - L* \sin \theta)/ \Delta t $$

$$ V_R=(V* \Delta t + L* \sin \theta/ \Delta t $$

## 1.2 逆解--航迹推演

上述从驱动轮速度解算为机器人线、角速度的过程，即为逆解，用于上传机器人里程计信息到ROS中，该里程计信息表示的机器人位姿在导航中有着非常重要的作用。

![dkbot02_04](http://www.dengke.xyz/usr/uploads/2019/11/3632550788.jpg)

DKbot使用了编码器电机，其中的编码器输出信息可以直接计算出左右轮行进的距离$ \Delta X_L $和$ \Delta X_R $。

对于线速度，在机器人坐标系中，可得机器人行进的距离为$ (\Delta X_L+\Delta X_R)/2 $，则映射到空间坐标系可得：

$$ \Delta X = \cos \theta * (\Delta X_L+\Delta X_R)/2 $$

$$ \Delta Y = \sin \theta * (\Delta X_L+\Delta X_R)/2 $$

对于角速度，根据近似原则可得：

$$ \Delta \theta = sin(\Delta \theta)=((\Delta X_R - \Delta X_L)/2)/l $$

综上所述，$ \Delta t $时间内机器人里程信息为：

$$ X = X + \Delta X = X+ \cos \theta*(\Delta X_L+\Delta X_R)/2 $$

$$ Y = Y + \Delta Y = Y+ \sin \theta*(\Delta X_L+\Delta X_R)/2 $$

$$ \theta = \theta + \Delta θ = θ+((\Delta X_R-\Delta X_L)/2)/l $$

&#8195;&#8195;
***
&#8195;&#8195;

# 第2章 底层驱动实现

DKbot使用ros_arduino_bridge框架实现底层驱动，该框架详细信息参考：
http://wiki.ros.org/ros_arduino_bridge
https://github.com/hbrobotics/ros_arduino_bridge/

## 2.1 安装

### 2.1.1 python-serial

ros_arduino_bridge主要使用python实现，因此树莓派和Arduino需要python-serial支持，安装python-serial包：

```
sudo apt-get install python-serial
```

### 2.1.2 ros_arduino_python

在相应目录下安装ros_arduino_bridge功能包集：

```shell
cd ~/catkin_workspace/src/dkbot_driver
git clone https://github.com/hbrobotics/ros_arduino_bridge.git
```

安装完后可将ros_arduino_firmware/src/libraries/ROSArduinoBridge拷贝出去，该目录下提供的内容是核心固件，可在Linux活Windows下使用Arduino IDE进行二次开发。

### 2.1.3 udev

Ubuntu MATE自带了udev工具，如果没有安装即可。DKbot使用udev规则设置各设备的串口规则。

安装完后，编辑rules文件：

```shell
cd /etc/udev/rules.d
sudo nano dkbot.rules
```

添加如下内容，Arduino串口名常为ttyACM*，赋予权限666，设置别名dkbotarduino：

```shell
KERNEL=="ttyACM?", SUBSYSTEM=="tty", MODE="0666" SYMLINK+="dkbotarduino"
```

## 2.2 代码结构分析

ros_arduino_bridge功能包集主要内容及作用如下：

![dkbot02_05](http://www.dengke.xyz/usr/uploads/2019/11/4065544116.png)

## 2.3 Arduino--创建驱动程序

以ros_arduino_firmware/src/libraries/ROSArduinoBridge为基础进行进行开发，主要设置PID参数、电机驱动、以下图方式完成正解--速度分解。

Arduino IDE中添加库：PID、Encoder

完整代码：
https://github.com/deng-ke/DKbot/tree/master/dkbot_driver/ros_arduino_bridge/ros_arduino_firmware/src/DKbotDriver

![dkbot02_06](http://www.dengke.xyz/usr/uploads/2019/11/1690281015.png)

## 2.4 ROS--配置节点

（1）编辑ros_arduino_python/config路径下的YAML文件：

```shell
roscd ros_arduino_python/config
cp arduino_params.yaml my_arduino_params.yaml
sudo nano my_arduino_params.yaml
```

（2）修改my_arduino_params.yaml代码为如下所示，主要重定义机器人尺寸、PID参数、启用base_controller：

```C++
# For a direct USB cable connection, the port name is typically
# /dev/ttyACM# where is # is a number such as 0, 1, 2, etc
# For a wireless connection like XBee, the port is typically
# /dev/ttyUSB# where # is a number such as 0, 1, 2, etc.

port: /dev/dkbotarduino
baud: 115200
timeout: 0.2

rate: 30
sensorstate_rate: 10

use_base_controller: True
base_controller_rate: 10

# For a robot that uses base_footprint, change base_frame to base_footprint
base_frame: base_link

# === Robot drivetrain parameters
wheel_diameter: 0.115
wheel_track: 0.45
encoder_resolution: 7392 # from Pololu for 131:1 motors
gear_reduction: 1.0
#motors_reversed: True

# === PID parameters
#Kp: 10
#Kd: 12
#Ki: 0
#Ko: 50
#accel_limit: 1.0

# === Sensor definitions.  Examples only - edit for your robot.
#     Sensor type can be one of the follow (case sensitive!):
#	  * Ping
#	  * GP2D12
#	  * Analog
#	  * Digital
#	  * PololuMotorCurrent
#	  * PhidgetsVoltage
#	  * PhidgetsCurrent (20 Amp, DC)



sensors: {
  #motor_current_left:   {pin: 0, type: PololuMotorCurrent, rate: 5},
  #motor_current_right:  {pin: 1, type: PololuMotorCurrent, rate: 5},
  #ir_front_center:      {pin: 2, type: GP2D12, rate: 10},
  #sonar_front_center:   {pin: 5, type: Ping, rate: 10},
  #arduino_led:          {pin: 13, type: Digital, rate: 5, direction: output}
}
```

## 2.5 测试

（1）运行arduino.launch文件，该文件中包含了arduino节点以及2.4节中配置的yaml参数文件：

```shell
roslaunch ros_arduino_python arduino.launch
```

（2）发布Twist消息控制DKbot，此时DKbot会原地旋转：

```shell
rostopic pub -r 1 /cmd_vel geometry_msgs/Twist "linear:
  x: 0.0
  y: 0.0
  z: 0.0
angular:
  x: 0.0
  y: 0.0
  z: 1.0"
```

&#8195;&#8195;
***
&#8195;&#8195;

# 第3章 键盘控制

下载安装teleop_twist_keyboard包并编译，使用以下命令启动键盘控制程序，通过键盘发布线、角速度，从而控制DKbot运动：

```shell
rosrun teleop_twist_keyboard teleop_twist_keyboard.py
```

![dkbot02_07](http://www.dengke.xyz/usr/uploads/2019/11/1668480802.png)

查看节点和话题关系：

![dkbot02_08](http://www.dengke.xyz/usr/uploads/2019/11/1180738435.png)
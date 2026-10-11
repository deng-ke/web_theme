本文记录 DKbot 的硬件组成和软件架构，包括硬件清单、模块连线、系统部署、通信方式。

<!--more-->

# 第 1 章 硬件组成

DKbot 外观图：

<img src="http://www.dengke.xyz/usr/uploads/2019/10/406770861.jpg" width = "320" height = "240" />

<img src="http://www.dengke.xyz/usr/uploads/2019/10/2923132940.jpg" width = "320" height = "240" />

## 1.1 清单

DKbot 主要模块清单如下：

图片 | 名称 | 数量 | 用途
-|-|-|-
![dkbot1_03](http://www.dengke.xyz/usr/uploads/2019/10/2561490272.png) | RPLIDAR A1激光雷达 | 1 | SLAM、导航功能核心器件；提供2D激光数据
![dkbot1_04](http://www.dengke.xyz/usr/uploads/2019/10/1841255182.png) | Raspberry Pi 3 | 1 | 主控制器；运行Ubuntu Mate 16.04 + ROS kinetic
![dkbot1_05](http://www.dengke.xyz/usr/uploads/2019/10/949583137.png) | Arduino Mega | 1 | 运行ros_arduino_bridge驱动程序；控制电机驱动模块；采集电机编码器数据
![dkbot1_06](http://www.dengke.xyz/usr/uploads/2019/10/2932135910.jpg) | 两路电机驱动模块 | 1 | 驱动底盘两路电机；DKbot中只有一块两路驱动板
![dkbot1_07](http://www.dengke.xyz/usr/uploads/2019/10/3698772719.jpg) | 编码器电机 | 4 | 驱动车轮；编码器输出里程计信息
![dkbot1_08](http://www.dengke.xyz/usr/uploads/2019/10/3061439225.png) | 11.1V/10000mA电池 | 1 | 系统电源
![dkbot1_09](http://www.dengke.xyz/usr/uploads/2019/10/3976858697.png) | 5V稳压模块 | 1 | 电池降压后为系统供电

## 1.2 连线

### 1.2.1 树莓派

树莓派是 DKbot 的主控制器，需连接底层驱动，并驱动激光雷达，因此通过 USB 与 Arduino Mega、RPLIDAR A1 连接。此外使用 5V 稳压模块的输出给树莓派供电。如下图：

![dkbot1_10](http://www.dengke.xyz/usr/uploads/2019/10/2876997507.png)

### 1.2.2 Arduino

Arduino Mega 是 DKbot 底层驱动的控制板，主要连接两路电机驱动模块以及编码器电机。连线如下图。

![dkbot1_11](http://www.dengke.xyz/usr/uploads/2019/10/4198816370.png)

编码器电机的 M2、M1 分别为电机驱动正负极接口，GND、C1、C2、VCC 分别为编码器码盘的电源地、A 相、B 相、电源正。

图中， Arduino Mega 的 18-21 口采集编码器信息作为系统的里程计，6、9 输出 PWM 信息给电机驱动模块，7、8、10、11 分别作为电机驱动模块的控制信号。电机驱动模块的输出口同时驱动两个电机，因此 DKbot 虽是四轮，但是两驱。

### 1.2.3 电源

电源系统中电池直接给电机驱动模块供电，5V 稳压模块的输出给 Arduino Mega、Raspberry Pi 3、电机的编码器码盘供电。

![dkbot1_12](http://www.dengke.xyz/usr/uploads/2019/10/2086060826.png)

# 第 2 章 软件架构

由于树莓派性能有限，DKbot 只在树莓派运行部分程序，另一部分在虚拟机中运行，二者通过 WiFi 连接并且需处于同一局域网中。树莓派与底层驱动采用 ROS 提供的 ros_arduino_bridge 方式进行通信。

![dkbot1_13](http://www.dengke.xyz/usr/uploads/2019/10/2412066824.png)

## 2.1 系统部署

### 2.1.1 安装 ROS

&#8196;&#8195;参考：http://www.dengke.xyz/archives/17/#menu_index_5

虚拟机：安装 Ubuntu 虚拟机，并安装 ros-kinetic-desktop-full。

树莓派：安装 Ubuntu MATE 16.04，并安装 ros-kinetic-ros-base 或 ros-kinetic-desktop

### 2.1.2 创建工作空间

参考：http://www.dengke.xyz/archives/17/#menu_index_16

在树莓派和虚拟机中均创建好工作空间，并添加环境变量。

## 2.2 通信方式

### 2.2.1 多机通信网络配置

参考：http://www.dengke.xyz/archives/17/#menu_index_31

配置前，在路由器中为 DKbot 分配静态 IP，以免日后开发中经常修改配置。

### 2.2.2 辅助工具

为了能在 PC 端远程登陆到机器人进行操作，安装 xrdp 和 ssh 工具。其中 xrdp 使用 Windows 自带远程桌面连接登录到 DKbot，ssh 在 Ubuntu 虚拟机中实现登录。


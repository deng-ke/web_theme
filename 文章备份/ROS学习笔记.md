# 第 1 章 ROS 架构

&#8195;&#8195;ROS 架构上分为三个层级：

&#8195;&#8195;（1）计算图级（Computation Graph level）：体现进程与系统的关系，描述系统怎么运行。

&#8195;&#8195;（2）文件系统级（Filesystem level）：组织构建程序文件。

&#8195;&#8195;（3）社区级（Community level）：开源社区，共享知识、算法和代码等。

## 1.1 计算图级

&#8195;&#8195;计算图级包含：节点、节点管理器、参数服务器、消息、服务、主题和消息记录包。
&#8195;&#8195;（1）节点（node）：主要的计算执行进程，可自行创建节点，加入网络。
&#8195;&#8195;（2）节点管理器（master）：是节点通信的基础，用于节点的注册、查找。
&#8195;&#8195;（3）参数服务器（parameter server）：属于节点管理器的一部分，将数据存放在系统关键位置，节点可以获取这些数据来配置、改变自己的状态。
&#8195;&#8195;（4）消息（message）：具有各种类型，节点间沟通传输的信息数据。
&#8195;&#8195;（5）主题（topic）：一类消息的集合，节点可以发布、订阅主题。发布者和订阅者之间相互解耦，不知对方的存在。
&#8195;&#8195;（6）服务（service）：区别于主题，服务用于节点直接向节点交互，获得请求或应答。
&#8195;&#8195;（7）消息记录包（bag）：保存回放消息数据的文件格式。
&#8195;&#8195;ROS 创建一个网络（Computation Network）来进行计算，该网络连接了系统中的所有进程（节点），如下图所示：
![ros_1_1.png](http://www.dengke.xyz/usr/uploads/2019/06/4251876124.png)

## 1.2 文件系统级

![ros_1_2.png](http://www.dengke.xyz/usr/uploads/2019/06/2558871114.png)
&#8195;&#8195;功能包（package）：一种特定结构的文件、文件夹的组合。ROS 中各种软件的组织方式，包含节点、配置文件、ROS 依赖库、第三方软件及其它任何逻辑构成。
&#8195;&#8195;源功能包（Metapackage）：只有一个文件（package.xml）的特殊包，将多个具有某些功能的包组织在一起形成逻辑包。

## 1.3 社区级

&#8195;&#8195;开源社区级，通过独立的网络社区分享软件、知识，包含如下四部分：
&#8195;&#8195;（1）发行版：可独立安装、类似于 Linux。
&#8195;&#8195;（2）软件库：联网的代码存储库，不同机构可以发布。
&#8195;&#8195;（3）ROS Wiki：论坛，可贡献自己的文件。
&#8195;&#8195;（4）Bug 提交系统
&#8195;&#8195;（5）邮件列表：交流软件更新等各种问题。
&#8195;&#8195;（6）博客：发布一些定期更新、新闻。
&#8195;&#8195;（7）ROS 问答

# 第 2 章 ROS 安装

## 2.1 版本选择

&#8195;&#8195;与接触过的 Contiki 系统一样，ROS 通常寄生在 Linux 下。对 ROS 兼容性最好的是 Ubuntu，这也是官方推荐。在 Ubuntu 下，我们可以直接使用 apt-get 快捷安装各版本各套餐的 ROS，当然有功夫折腾也可挑战源码安装。

&#8195;&#8195;**版本问题**：Ubuntu 有各种版本，当然 ROS 也是，想详细了解的看官请移步 http://wiki.ros.org/Distributions。官方介绍了 ROS 各发行版本，以及它们分别需要对应的 Ubuntu 版本。
![ros_2_1](http://www.dengke.xyz/usr/uploads/2019/06/4160169375.png)
&#8195;&#8195;**套餐问题**：每个版本的 ROS 都有三种套餐供大家选择，分别是桌面完整版、桌面版、基础版。详见下表：

&#8195;&#8195;**我的选择**：Dkbot 初期采用树莓派 3 作为主控板。综合考虑树莓派性能和开发方便，在树莓派安装基础版，在 PC 安装桌面完整版，版本都为 ROS Kinetic。这样可在 PC 远程登录到树莓派进行开发，任何涉及到 GUI 的内容也都在 PC 运行。

## 2.2 安装过程

&#8195;&#8195;安装过程主要参考官方 wiki（http://wiki.ros.org/kinetic/Installation/Ubuntu），但是在 Ubuntu 虚拟机中会出现一些问题。下述为 Ubuntu 虚拟机中 ROS 的安装过程，树莓派中的安装过程基本相同。

### 2.2.1 安装虚拟机

&#8195;&#8195;虚拟机中安装 Ubuntu 的过程不再赘述，我的主要配置如下：

* 虚拟机软件：VMware
* Ubuntu 版本：16.04.2
* 内存：2G（建议不小于 2G）
* 处理器数量：2
* 磁盘空间：100G

### 2.2.2 设置软件源

&#8195;&#8195;（1）配置软件中心：确保 Ubuntu 软件中心“restricted”、“universe”、 “multiverse”三个选项前都打上勾。
![ros_2_2](http://www.dengke.xyz/usr/uploads/2019/06/1692276596.png)

&#8195;&#8195;（2）向 source.list 中添加软件源，并设置密钥。（如果网络不稳定，可以查找国内源和密钥）

```shell
sudo sh -c 'echo "deb http://packages.ros.org/ros/ubuntu $(lsb_release -sc) main" > /etc/apt/sources.list.d/ros-latest.list'
sudo apt-key adv --keyserver hkp://ha.pool.sks-keyservers.net:80 --recv-key 421C365BD9FF1F717815A3895523BAEEB01FA116
```

### 2.2.3 安装

&#8195;&#8195;（1）更新软件源。

```shell
sudo apt-get update
```

&#8195;&#8195;（2）安装：虚拟机中安装桌面完整版，树莓派中安装基础版。（安装过程用时较长）

```shell
sudo apt-get install ros-kinetic-desktop-full（虚拟机中）
sudo apt-get install ros-kinetic-ros-base（树莓派中）
```

### 2.2.4 初始化 rosdep

&#8195;&#8195;rosdep 是 ROS 中自带的一个命令行工具。使用它，我们可以方便地为要编译的源代码安装系统依赖项，并且在 ROS 中运行一些核心组件时也需要它。
&#8195;&#8195;安装 ROS 时已自动安装 rosdep，此处需要进行初始化并更新（执行第二条命令更新时，一定不能加 sudo，否则会导致权限错误）：

```shell
sudo rosdep init
rosdep update
```

### 2.2.5 设置环境变量

&#8195;&#8195;设置环境变量到“.bashrc”文件中，并加载此设置。

```shell
echo "source /opt/ros/kinetic/setup.bash" >> ~/.bashrc
source ~/.bashrc
```

### 2.2.6 安装常用工具

&#8195;&#8195;下面几个是特别常用的工具，推荐先安装，当然日后需要什么工具可再单独安装。

```shell
sudo apt-get install python-rosinstall python-rosinstall-generator python-wstool build-essential
```

### 2.2.7 测试

&#8195;&#8195;打开一个 Terminal，运行下面命令，并保持运行；

```shell
roscore
```

&#8195;&#8195;再打开一个 Terminal，运行下面命令，出现如图所示一只随机颜色的小乌龟，保持该命令运行；

```shell
rosrun turtlesim turtlesim_node
```

&#8195;&#8195;再打开一个 Terminal，运行下面命令，并在该 Terminal 中使用键盘方向键控制小乌龟运动

```shell
rosrun turtlesim turtle_teleop_key
```

![ros_2_3](http://www.dengke.xyz/usr/uploads/2019/06/1598650487.png)

## 2.3 遇见的坑

&#8195;&#8195;我安装并测试完成后，重启下虚拟机。结果虚拟机在开机时，无法进入图形桌面，出现错误：The system is running in low-graphics mode…

![ros_2_4](http://www.dengke.xyz/usr/uploads/2019/06/3718700996.png)

&#8195;&#8195;网上查了下，第一次看到的是说 ROS 需要的内存等性能较高，建议给虚拟机分配的性能高点。
&#8195;&#8195;后来再看到大部分说法是虚拟机显卡驱动的问题，于是尝试了方法 1：在出现错误的界面按下快捷键 ctrl+alt+F1，进入命令行依次执行下述三条命令，即手动配置 X server

```shell
cd /etc/X11
sudo cp xorg.conf.failsafe xorg.conf
sudo reboot
```

&#8195;&#8195;尝试方法 1 后发现虚拟机能正常启动了，但是该方法未彻底解决问题，会导致 VMware tools 失效，即虚拟机不能全屏。（其实现在的 X server 会自动配置的，怀疑是我们的手动配置导致 VMware tools 失效）
&#8195;&#8195;最终找到彻底解决的**办法 2**：同方法 1 进入命令行，依次执行下述命令，将驱动及一些依赖文件全部更新, 虽然暴力但是可以彻底解决问题。

```shell
sudo apt-get update
sudo apt-get upgrade
```

# 第 3 章 工作空间 & 功能包

## 3.1 工作空间

&#8195;&#8195;一切还要从那只小海龟讲起。记得在安装 ROS 时，我利用 turtlesim 这个包来测试安装结果，如果正常则是出现一只随机颜色的小海龟，并且可以利用键盘控制它移动。
&#8195;&#8195;那么问题来了，像 turtlesim 这种系统自带的功能包被存放在哪的呢？可以使用如下命令查找(元)功能包的路径：

```shell
rospack find turtlesim
```

![ros_3_1](http://www.dengke.xyz/usr/uploads/2019/06/1195393628.png)
&#8195;&#8195;与此相关还有两个命令：rosls、roscd，对照 Linux 的 ls、cd 命令就能理解他们了，二者使用结果如图所示：
![ros_3_2](http://www.dengke.xyz/usr/uploads/2019/06/4234909520.png)

### 3.1.1 创建工作空间

&#8195;&#8195;为了便于管理开发中的各种代码，首先创建工作空间。系统在初始时有一个默认的工作空间，可用如下命令查看，返回的结果正是 turtlesim 包所在的路径。

```shell
echo $ROS_PACKAGE_PATH
```

&#8195;&#8195;（1）依次执行如下三条命令，新建工作空间文件夹，并初始化：

```shell
mkdir -p ~/catkin_ws/src
cd catkin_ws/src/
catkin_init_workspace
```

![ros_3_3](http://www.dengke.xyz/usr/uploads/2019/06/1603930896.png)
&#8195;&#8195;（2）初始化后文件夹中只有 CmakeList.txt 文件，接着编译工作空间，使用如下两条命令：

```shell
cd ~/catkin_ws/
catkin_make
```

&#8195;&#8195;（3）重新加载 setup.bash 文件。

```shell
source devel/setup.bash
```

&#8195;&#8195;（4）添加新环境变量。

```shell
echo "source ~/catkin_ws/devel/setup.bash" >> ~/.bashrc
```

## 3.2 功能包

&#8195;&#8195;参考链接：http://wiki.ros.org/catkin/conceptual_overview。
&#8195;&#8195;创建工作空间的过程中用到了 catkin 相关的命令，查相关资料得知：catkin 是 ROS 现有的官方构建系统，是旧构建系统 rosbuild 的继承者。catkin 结合了 CMake 宏和 Python 脚本，在 CMake 的正常工作流程之上提供了一些功能。将使用 catkin 来创建(元)功能包：

### 3.2.1 创建(元)功能包

&#8195;&#8195;（1）首先需要进入工作空间 src 目录：

```shell
cd catkin_ws/src
```

&#8195;&#8195;（2）使用 catkin_create_pkg 创建功能包，其命令格式包含包名和依赖项。

```shell
catkin_create_pkg [package_name] [dependency1]…[dependencyN]
catkin_create_pkg hello_world std_msgs roscpp
```

![ros_3_4](http://www.dengke.xyz/usr/uploads/2019/06/2372597878.png)
&#8195;&#8195;上述创建的包名称为 hello_world，依赖项为：

```shell
std_msgs：包含常见消息类型，表示基本数据类型和其它基本消息构造；
roscpp：使用C++实现的ROS各种功能，提供一个客户端供开发者调用接口完成开发。
```

&#8195;&#8195;（3）检查下创建结果，查看包的依赖项。这里查看的是直接依赖项，也可使用 depends 查看所有依赖项（直接 + 间接）。

```shell
rospack depends1 hello_world
```

### 3.2.2 编译

&#8195;&#8195;（1）编译功能包（若编译 src 中全部包则执行 catkin_make）：

```shell
cd ~/catkin_ws/
catkin_make --pkg hello_world
```

&#8195;&#8195;（2）通常编译完后会发生变化，因此建议识别新目录，该命令会通知用户 ROS 中新添加的内容：

```shell
rospack profile
```

![ros_3_5](http://www.dengke.xyz/usr/uploads/2019/06/1246461976.png)

# 第 4 章 节点 & launch

## 4.1 创建节点

&#8195;&#8195;在 hello_world 包中创建两个节点：一个发布数据，另一个接收数据，并且编译与使用这两个节点。
&#8195;&#8195;进入 hello_world 包资源目录：

```shell
roscd hello_world/src/
```

&#8195;&#8195;创建两个 cpp 文件：

```shell
touch send.cpp receive.cpp
```

&#8195;&#8195;两个节点的全部代码参考：https://github.com/deng-ke/ROS-explore/。

### 4.1.1 发布节点

&#8195;&#8195;（1）初始化设置：

```C++
ros::init(argc, argv, "send");//初始化节点并设置名称
ros::NodeHandle n;//设置进程处理程序
ros::Publisher pub = n.advertise<std_msgs::String>("message", 1000);/*将节点初始化为发布者，并将所发布的主题和类型告知节点管理器，这里主题名为message，类型为String，1000为缓冲区大小*/
ros::Rate loop_rate(10);//设置发送数据的频率，这里为10Hz
```

&#8195;&#8195;（2）创建消息变量、发布消息：

```C++
std_msgs::String msg;//创建消息变量，类型为之前定义的String。
std::stringstream ss;
ss << " I am the example1_a node ";
msg.data = ss.str();
pub.publish(msg);//发布消息，使用(1)中定义的发布器
```

### 4.1.2 接收节点

&#8195;&#8195;（1）回调函数设置，节点收到消息后进行相应的数据处理。

```C++
void messageCallback(const std_msgs::String::ConstPtr& msg){
ROS_INFO("I heard: [%s]", msg->data.c_str());
}
```

&#8195;&#8195;（2）创建订阅者，从 message 主题订阅消息，并调用回调函数，这里的 1000 为缓冲区大小。

```C++
ros::Subscriber sub = n.subscribe("message", 1000, messageCallback);
```

## 4.2 编译

```shell
rosed hello_world CMakeLists.txt
```

&#8195;&#8195;（1）编辑 CMakeLists.txt 文件，相当于设置 makefile，要指定编译的文件。在初始文件中增加下述代码。

```C++
add_executable(send src/send.cpp)
add_executable(receive src/receive.cpp)
add_dependencies(send hello_world_generate_messages_cpp)
add_dependencies(receive hello_world_generate_messages_cpp)
target_link_libraries(send ${catkin_LIBRARIES})
target_link_libraries(receive ${catkin_LIBRARIES})
```

&#8195;&#8195;（2）编译整个包。

```shell
cd catkin_ws/
catkin_make --pkg hello_world
rospack profile
```

## 4.3 使用节点

&#8195;&#8195;（1）首先启动节点管理器、参数服务器、rosout 日志节点。更多关于 roscore：http://wiki.ros.org/roscore。

```shell
roscore
```

&#8195;&#8195;（2）分别启动 send、receive 节点，在运行 receive 的窗口中收到如下图所示信息。

```shell
rosrun hello_world send
rosrun hello_world receive
```

![ros_4_1](http://www.dengke.xyz/usr/uploads/2019/06/1268059930.png)
&#8195;&#8195;（3）可使用 rosnode 工具查看节点信息，查询 rosnode 用法。

```shell
rosnode
```

![ros_4_2](http://www.dengke.xyz/usr/uploads/2019/06/3072114591.png)

## 4.4 主题与节点

&#8195;&#8195;send、receive 节点通过主题进行交互：send 发布了主题/message，并向主题发布消息；receive 从/message 主题订阅消息。
&#8195;&#8195;使用 rostopic 工具查看主题相关信息，该工具相关用法：
![ros_4_3](http://www.dengke.xyz/usr/uploads/2019/06/951520277.png)

## 4.5 创建 launch 文件

&#8195;&#8195;之前创建了 send、receive 节点，启动时在三个 terminal 中依次启动了 roscore、send、receive，设想如果要同时启动 20 个节点，用同样的方法将很麻烦。
&#8195;&#8195;launch 文件便是针对上述问题出现的，使用它可以同时启动多个节点，并且启动 launch 文件时会自动启动 roscore。

&#8195;&#8195;（1）进入 hello_world 包所在目录，创建空白 launch 文件。

```shell
roscd hello_world/
mkdir launch
cd launch/
touch hello_world.launch
```

&#8195;&#8195;（2）编辑 launch 文件内容。

```xml
<?xml version="1.0"?>
<launch>
  <node name="send" pkg="hello_world" type="send"/>
  <node name="receive" pkg="hello_world" type="receive"/>
</launch>
```

&#8195;&#8195;launch 文件使用 xml 语言，最外面是 launch 标签，node 标签用于从指定的包中启动指点的节点。launch 文件还可有启动参数等多种标签，更多关于 launch：http://wiki.ros.org/roslaunch/XML/。

## 4.6 运行 launch 文件

&#8195;&#8195;（1）运行 launch 文件，可看到如下图所示，已启动两个节点。

```shell
roslaunch hello_world hello_world.launch
```

![ros_4_4](http://www.dengke.xyz/usr/uploads/2019/06/1241016307.png)
&#8195;&#8195;（2）使用 rostopic 检查运行结果。

```shell
rostopic list
rostopic echo /message
```

![ros_4_5](http://www.dengke.xyz/usr/uploads/2019/06/1875768641.png)

# 第 5 章 网络配置与多机通信

&#8195;&#8195;在 ROS 开发过程中，主控设备如树莓派、TK1、Intel 等都放在移动机器人平台上，因此直接操作主控设备很不方便。为此，ROS 提供了一种多机通信的配置方式，该方式更可以用于多机器人系统中。
&#8195;&#8195;以我开发中的配置为例：将 PC 和机器人配置为主从模式，前提是 PC 和机器人必须处于同一无线局域网下，且最好为同一网段(如果复杂网络中网段不同，可能出现各种问题)。建议给机器人分配静态 IP，可避免不断修改配置。如：
&#8195;&#8195;机器人(树莓派)：192.168.1.109(静态 IP)
&#8195;&#8195;PC(Ubuntu)：192.168.1.100

## 5.1 PC 端配置

&#8195;&#8195;编辑/ets/hosts 文件，增加一条配置，其 IP 即为机器人的 IP。

```shell
sudo vim /etc/hosts
```

![ros_5_1.png](http://www.dengke.xyz/usr/uploads/2019/06/2022180884.png)

&#8195;&#8195;.bashrc 文件增加配置如下，其中 ROS_IP 和 ROS_HOSTNAME 配置为 PC 本机的 IP，ROS_MASTER_URI 配置为机器人地址。

```shell
echo "export ROS_IP=\`hostname -I\`" >> ~/.bashrc
echo "export ROS_HOSTNAME=\`hostname -I\`" >> ~/.bashrc
echo "export ROS_MASTER_URI=http://robot.local:11311" >> ~/.bashrc
```

![ros_5_2.png](http://www.dengke.xyz/usr/uploads/2019/06/290363451.png)

## 5.2 机器人端配置

&#8195;&#8195;配置.bashrc 文件，同 PC 端，把 ROS_MASTER_URI 指向机器人即可。

```shell
echo "export ROS_IP=\`hostname -I\`" >> ~/.bashrc
echo "export ROS_HOSTNAME=\`hostname -I\`" >> ~/.bashrc
echo "export ROS_MASTER_URI=http://\`hostname -I\`:11311" >> ~/.bashrc
```

![ros_5_3.png](http://www.dengke.xyz/usr/uploads/2019/06/2058234250.png)

## 5.3 测试

&#8195;&#8195;用之前创建的 hello_world 包来测试。在 PC 端运行 send 节点；再利用 ssh 远程登录到机器人，运行 receive 节点。机器人端收到消息，测试成功。

# 第 6 章 常用工具

## 6.1 rqt_graph 检测节点状态

&#8195;&#8195;rqt_graph 可绘制有向图来显示 ROS 会话的当前状态，包括节点、主题以及连接状态等信息。通常一个系统运行起来，会有多个节点相互发布消息，利用 rqt_console 检测系统运行状态并帮助调试是十分必要的。

&#8195;&#8195;使用 hello_world 包，运行 hello_world.launch。

```shell
roslaunch hello_world hello_world.launch
```

&#8195;&#8195;再开一个终端，启动 rqt_graph。

```shell
rosrun rqt_graph rqt_graph
```

![ros_6_1.png](http://www.dengke.xyz/usr/uploads/2019/06/1031772666.png)
&#8195;&#8195;图中显示的是 send、receive 节点通过/message 主题的交互情况，界面中部有一排勾选项。取消勾选 Hide：Debug 项，可得到完整的结构。

* rqt_gui_py_node_4250：rqt_graph 本身节点。
* rosout：用于收集和记录各节点调试输出信息的节点，启动 roscore 后，该节点就运行。

![ros_6_2.png](http://www.dengke.xyz/usr/uploads/2019/06/290442494.png)

&#8195;&#8195;当系统出现问题，节点会一直以红色显示，此时可在左上角选择“Node/Topics(all)”，查看节点之间连接是否中断。

![ros_6_3.png](http://www.dengke.xyz/usr/uploads/2019/06/2066016889.png)

&#8195;&#8195;当节点不在同一机器运行时，rqt_graph 则可显示各节点能否从各自的机器连接到对方。

![ros_6_4.png](http://www.dengke.xyz/usr/uploads/2019/06/1412242647.png)

## 6.2 rqt_console 日志消息可视化

&#8195;&#8195;rqt_console 可对日志消息进行可视化、过滤和分析。比如运行 hello_world.launch 后，可通过 rqt_console 查看 send、receive 节点之间传输的消息。
&#8195;&#8195;启动 hello_world.launch 后，执行：

```shell
rosrun rqt_console rqt_console
```

![ros_6_5.png](http://www.dengke.xyz/usr/uploads/2019/06/4240190248.png)

## 6.3 rqt_reconfigure 重配置动态参数

&#8195;&#8195;本节主要涉及三个文件：reconfigure.cfg、reconfigure.cpp、CMakeLists.txt。全部代码见：
&#8195;&#8195;动态参数原本是为驱动程序设计，比如激光雷达、摄像头、IMU 等驱动程序中的配置参数，很多场景下，例如参数调试、功能切换等，我们需要动态更新这些参数。
&#8195;&#8195;但是 ROS 中的参数服务器无法在线动态更新。比如节点 A、B 通信，B 如果不主动查询参数值，就无法得知 A 是否修改了参数。为解决这一问题，ROS 提供了 dynamic_reconfigure 功能包。

&#8195;&#8195;（1）首先创建功能包 reconfigure_test，其中依赖项为 rospy、roscpp、dynamic_reconfigure。
&#8195;&#8195;（2）在包中新建 cfg 文件夹，在包中创建 reconfigure.cfg 配置文件。
&#8195;&#8195;（3）在 src 文件夹中创建 reconfigure.cpp 文件。
&#8195;&#8195;（4）修改 CMakeLists.txt 文件
&#8195;&#8195;（5）依次执行命令，进入 rqt_reconfigure 界面，5 个设定的可调参数

```shell
roscore
rosrun reconfigure_test reconfigure
rosrun rqt_reconfigure rqt_reconfigure
```

![ros_6_6.png](http://www.dengke.xyz/usr/uploads/2019/06/526829074.png)

### 6.3.1 reconfigure.cfg

&#8195;&#8195;主要部分代码：

&#8195;&#8195;（1）定义需要动态配置的参数。这里使用参数生成器的 add(name,type, level,description, default, min,max)方法定义了四个不同类型的参数，各形参含义如下：

* name：参数名，使用字符串描述；
* type：参数类型，可以是 int_t, double_t, str_t, 或者 bool_t；
* level：需要传入参数动态配置回调函数中的掩码，在回调函数中会修改所有参数的掩码，表示参数已经进行修改；
* description：描述参数作用的字符串；
* default：设置参数的默认值；
* min：可选，设置参数的最小值，对于字符串和布尔类型值不生效；
* max：可选，设置参数的最大值，对于字符串和布尔类型值不生效；

```C++
gen.add("int_param",    int_t,    0, "An Integer parameter", 50,  0, 100)
gen.add("double_param", double_t, 0, "A double parameter",    .5, 0,   1)
gen.add("str_param",    str_t,    0, "A string parameter",  "Hello World")
gen.add("bool_param",   bool_t,   0, "A Boolean parameter",  True)
```

&#8195;&#8195;（2）生成一个枚举类型的参数值。这里定义了一个 int_t 类型得到参数“size”，该参数的值可以通过一个枚举列出来。枚举的定义使用 enum 方法进行定义，其中使用 const 方法定义每一个枚举值的名称、类型、值、描述字符串。

```C++
size_enum = gen.enum([ gen.const("Small",      int_t, 0, "A small constant"),
                      gen.const("Medium",     int_t, 1, "A medium constant"),
                      gen.const("Large",      int_t, 2, "A large constant"),
                      gen.const("ExtraLarge", int_t, 3, "An extra large constant")], "An enum to set size")
gen.add("size", int_t, 0, "A size parameter which is edited via an enum", 1, 0, 3, edit_method=size_enum)
```

&#8195;&#8195;（3）退出程序，并生成所有 C++ 和 Python 相关文件。第二个参数表示节点名，第三个参数是生成文件所使用的前缀，需要和配置文件名相同。

```C++
exit(gen.generate(PACKAGE, "reconfigure_test", " reconfigure"))
```

### 6.3.2 reconfigure.cpp

&#8195;&#8195;主要部分代码：

&#8195;&#8195;（1）回调函数。回调函数的传入参数有两个，一个是新的参数配置值，另外一个表示参数修改的掩码。然后在回调函数中将修改后的参数值打印出来。

```C++
void callback(reconfigure_test::TutorialsConfig &config, uint32_t level) {
  ROS_INFO("Reconfigure Request: %d %f %s %s %d", 
            config.int_param, config.double_param, 
            config.str_param.c_str(), 
            config.bool_param?"True":"False", 
            config.size);
}
```

&#8195;&#8195;（2）首先初始化 ROS 节点，然后创建了一个参数动态配置的服务端实例，参数配置的类型就是配置文件中描述的类型。该服务端实例会监听客户端的参数配置请求。
&#8195;&#8195;然后定义一个回调函数，并将回调函数和服务端绑定，当客户端请求修改参数时，服务端即可跳转到回调函数中进行处理。

```C++
ros::init(argc, argv, "reconfigure_test");
dynamic_reconfigure::Server<reconfigure_test::TutorialsConfig> server;
dynamic_reconfigure::Server<reconfigure_test::TutorialsConfig>::CallbackType f;
f = boost::bind(&callback, _1, _2);
server.setCallback(f);
```

### 6.3.3 CMakeLists.txt

&#8195;&#8195;添加编译规则如下：

```C++
cmake_minimum_required(VERSION 2.8.3)
project(reconfigure_test)

find_package(catkin REQUIRED COMPONENTS
  dynamic_reconfigure
  roscpp
  rospy
)

generate_dynamic_reconfigure_options(
   cfg/reconfigure.cfg
 )

catkin_package(
  CATKIN_DEPENDS dynamic_reconfigure roscpp rospy
)

include_directories(
  ${catkin_INCLUDE_DIRS}
)

add_executable(reconfigure src/reconfigure.cpp)
add_dependencies(reconfigure reconfigure_test_generate_messages_cpp)
target_link_libraries(reconfigure ${catkin_LIBRARIES})
```

# 第 7 章 3D 可视化工具

&#8195;&#8195;rviz 是 ROS 中 3D 可视化工具，利用该工具可实现各类数据可视化，比如机器人模型、图像、地图、点云、激光：http://wiki.ros.org/rviz

## 7.1 安装与运行

&#8195;&#8195;桌面完整版已经集成 rviz，其它版本需要单独安装：

```shell
sudo apt-get install ros-kinetic-rviz
```

&#8195;&#8195;由于 GUI 的原因，树莓派中无法启动 rviz，如果机器人主控板为树莓派，则需要在 PC 端运行 rviz，且将 PC 与树莓派配置为多机通信模式。启动 rviz：

```shell
roscore
rosrun rviz rviz
```

&#8195;&#8195;打开后，“Displays”列表中已有“Global Options”、“Global Status”、“Grid”三项，其中“Global Options”中为全局变量，“Global Status”中的“Fixed Frame”为世界坐标系，通常为 map。

![ros_7_1.png](http://www.dengke.xyz/usr/uploads/2019/07/1315177328.png)

## 7.2 实现数据可视化

&#8195;&#8195;rviz 提供两种方式进行配置，以实现各类数据可视化：逐一添加各插件、导入已配置好插件的.rviz 文件。

### 7.2.1 添加插件

&#8195;&#8195;点击“Add”按钮，在列表中选择需要添加的插件，并可添加描述和名称。

![ros_7_2.png](http://www.dengke.xyz/usr/uploads/2019/07/1178886100.png)
&#8195;&#8195;添加插件后，“Display”列表中即可显示该插件，其中最重要的是设置该插件订阅的话题。
![ros_7_3.png](http://www.dengke.xyz/usr/uploads/2019/07/669982227.png)
&#8195;&#8195;rviz 支持的数据类型与插件如下表所示：

套餐 | apt安装包名 | 包含内容 | 说明
-|-|-|-

名称 | 描述 | 使用的消息
-|-|- 
Axes | 显示一组坐标轴	
Effort | 显示正施加在机器人每个旋转关节上的力 | sensor_msgs/JointStates
Camera | 创建一个新窗口，显示摄像头图像 | sensor_msgs/Image，sensor_msgs/CameraInfo
Grid | 显示2D或3D的栅格	
Grid Cells | 绘制一个栅格单元，通常是来自导航包中的代价地图中的障碍物 | nav_msgs/GridCells
Image	| 创建一个新窗口，显示图像 | sensor_msgs/Image
InteractiveMaker | 显示源自一个或者多个交互Marker服务器的3D物体并且允许鼠标交互 | visualization_msgs/InteractiveMarker
Laser Scan | 显示激光数据 | sensor_msgs/LaserScan
Map | 显示地图 | nav_msgs/OccupancyGrid
Markers | 允许程序员通过话题显示任意特征的形状 | visualization_msgs/Marker, visualization_msgs/MarkerArray
Path | 显示导航过程中的路径信息 | nav_msgs/Path
Point | 使用圆球体绘制一个点 | geometry_msgs/PointStamped
Pose | 将位姿绘制为一个箭头或者坐标轴 | geometry_msgs/PoseStamped
Pose Array | 根据位姿列表，绘制一组位姿箭头 | geometry_msgs/PoseArray
Point Cloud2 | 显示点云数据 | sensor_msgs/PointCloud, sensor_msgs/PointCloud2
Polygon | 绘制多边形轮廓 | geometry_msgs/Polygon
Odometry | 绘制一段时间内里程计位姿 | nav_msgs/Odometry
Range | 使用锥形表示传感器的测量数据 | sensor_msgs/Range
RobotModel | 显示一个在正确位姿的机器人的视觉表示	
TF | 显示TF变换层级	
Wrench | 用箭头（力）和箭头+圆（转矩）绘制一个力旋量 | geometry_msgs/WrenchStamped

### 7.2.2 导入配置文件

&#8195;&#8195;添加好各种插件后，可将保存当前配置，保存后的文件后缀名为 rviz。之后再导入即可使用相同配置。

![ros_7_4.png](http://www.dengke.xyz/usr/uploads/2019/07/2656933054.png)


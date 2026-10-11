DKbot 已经可以通过键盘、语音控制进行基本运动，但还无法精确控制，比如控制其直线前进指定距离，或者旋转指定角度。
这里涉及到 DKbot 的线速度、角速度，这两个参数的精确度会影响到 DKbot 位姿的精确度，因此在机器人 SLAM、导航中都将起着重要作用。下面分别对 DKbot 的线速度、角速度进行校准，使其误差在可接受的范围内。

<!--more-->

# 第 1 章 线速度校准

## 1.1 校准原理与策略

** 线速度校准 **：发送指令控制机器人前进指定距离（比如 1m），测量 DKbot 实际前进距离与指定距离是否一致。

** 影响线速度的主要参数 **：轮子直径，即 my_arduino_params.yaml 文件中的 wheel_diameter 参数。

** 校准策略 **：如果实际前进距离大于指定距离，调大 wheel_diameter 参数，反之调小。

## 1.2 代码说明

完整代码：

https://github.com/deng-ke/DKbot/tree/master/dkbot_controller/dkbot_calibrate

### 1.2.1 calibrate_linear.py

（1）导入所需包。

```python
import rospy
from geometry_msgs.msg import Twist, Point
from math import copysign, sqrt, pow
from dynamic_reconfigure.server import Server
import dynamic_reconfigure.client
from dkbot_calibrate.cfg import CalibrateLinearConfig
import tf
```

（2）初始化节点，设置发布频率。

```python
rospy.init_node('calibrate_linear', anonymous=False)
# Set rospy to execute a shutdown function when terminating the script
rospy.on_shutdown(self.shutdown)
# How fast will we check the odometry values?
self.rate = rospy.get_param('~rate', 20)
r = rospy.Rate(self.rate)
```

（3）设置校准中控制参数的默认值，包含前进距离、速度、容忍误差、里程数据的缩放比例、开始测试的开关。这些参数的具体值将由 rqt_reconfigure 回传。

```python
self.test_distance = rospy.get_param('~test_distance', 1.0) # meters
self.speed = rospy.get_param('~speed', 0.15) # meters per second
self.tolerance = rospy.get_param('~tolerance', 0.01) # meters
self.odom_linear_scale_correction = rospy.get_param('~odom_linear_scale_correction', 1.0)
self.start_test = rospy.get_param('~start_test', True)
```

（4）向/cmd_vel 话题发布 Twist 消息，控制 DKbot 前进。

```python
self.cmd_vel = rospy.Publisher('/cmd_vel', Twist, queue_size=5)
```

（5）创建参数动态配置的服务端实例，并连接。

```python
# Fire up the dynamic_reconfigure server
dyn_server = Server(CalibrateLinearConfig, self.dynamic_reconfigure_callback)
# Connect to the dynamic_reconfigure server
dyn_client = dynamic_reconfigure.client.Client("calibrate_linear", timeout=60)
```

（6）初始化 base_frame、odom_frame、tf 监听。

```python
# The base frame is base_footprint for the TurtleBot but base_link for Pi Robot
self.base_frame = rospy.get_param('~base_frame', '/base_link')
# The odom frame is usually just /odom
self.odom_frame = rospy.get_param('~odom_frame', '/odom')
# Initialize the tf listener
self.tf_listener = tf.TransformListener()
```

（7）获取 DKbot 初始位置，以及位置获取函数。

```python
self.position = Point()
# Get the starting position from the tf transform between the odom and base frames
self.position = self.get_position()
x_start = self.position.x
y_start = self.position.y
move_cmd = Twist()

def get_position(self):
    # Get the current transform between the odom and base frames
    try:
        (trans, rot)  = self.tf_listener.lookupTransform(self.odom_frame, self.base_frame, rospy.Time(0))
    except (tf.Exception, tf.ConnectivityException, tf.LookupException):
        rospy.loginfo("TF Exception")
        return
return Point(*trans)
```

（8）循环中控制 DKbot 前进指定距离(test_distance)，循环中会不断计算当前位置与起点位置的距离，并判断该距离是否超过指定距离。

```python
while not rospy.is_shutdown():
            # Stop the robot by default
            move_cmd = Twist()
            if self.start_test:
                # Get the current position from the tf transform between the odom and base frames
                self.position = self.get_position()
                # Compute the Euclidean distance from the target point
                distance = sqrt(pow((self.position.x - x_start), 2) +
                                pow((self.position.y - y_start), 2))
                # Correct the estimated distance by the correction factor
                distance *= self.odom_linear_scale_correction
                # How close are we?
                error =  distance - self.test_distance
                # Are we close enough?
                if not self.start_test or abs(error) <  self.tolerance:
                    self.start_test = False
                    params = {'start_test': False}
                    rospy.loginfo(params)
                    dyn_client.update_configuration(params)
                else:
                    # If not, move in the appropriate direction
                    move_cmd.linear.x = copysign(self.speed, -1 * error)
            else:
                self.position = self.get_position()
                x_start = self.position.x
                y_start = self.position.y
            self.cmd_vel.publish(move_cmd)
            r.sleep()
```

（9）reconfigure 的回调函数，回传（3）中的控制参数。

```python
def dynamic_reconfigure_callback(self, config, level):
        self.test_distance = config['test_distance']
        self.speed = config['speed']
        self.tolerance = config['tolerance']
        self.odom_linear_scale_correction = config['odom_linear_scale_correction']
        self.start_test = config['start_test']
        return config
```

### 1.2.2 CalibrateLinear.cfg

定义需要动态配置的参数，参考：http://www.dengke.xyz/archives/1652。

```python
#! /usr/bin/env python
PACKAGE = "dkbot_calibrate"
from dynamic_reconfigure.parameter_generator_catkin import *
gen = ParameterGenerator()
gen.add("test_distance", double_t, 0, "Test distance in meters", 1.0, 0.0, 2.0)
gen.add("speed", double_t, 0, "Robot speed in meters per second", 0.15, 0.0, 0.3)
gen.add("tolerance", double_t, 0, "Error tolerance to goal distance in meters", 0.01, 0.0, 0.1)
gen.add("odom_linear_scale_correction", double_t, 0, "Linear correction factor", 1.0, 0.0, 3.0)
gen.add("start_test", bool_t, 0, "Check to start the test", False)
exit(gen.generate(PACKAGE, "calibrate_linear", "CalibrateLinear"))
```

### 1.2.3 CMakeLists.txt

编译规则中加入动态配置，以及 cfg 文件。

```python
cmake_minimum_required(VERSION 2.8.3)
project(dkbot_calibrate)
find_package(catkin REQUIRED dynamic_reconfigure)
generate_dynamic_reconfigure_options(
   cfg/CalibrateAngular.cfg
   cfg/CalibrateLinear.cfg
)
```

## 1.3 测试

分别运行以下命令：

```shell
roslaunch ros_arduino_python arduino.launch
rosrun dkbot_calibrate calibrate_linear.py
rosrun rqt_reconfigure rqt_reconfigure
```

进入动态配置界面后，点击 start_test，DKbot 开始前进，利用卷尺测量前进距离。再根据 1.1 节校准策略校准。

![dkbot4_01](http://www.dengke.xyz/usr/uploads/2019/12/200945545.png)

# 第 2 章 角速度校准

## 2.1 校准原理与策略

** 角速度校准 **：发送指令控制机器人旋转指定角度（比如 360 度），测量 DKbot 实际旋转角度与指定角度是否一致。

** 影响角速度的主要参数 **：轮子直径、轮间距，将线速度校准完后，只需调整轮间距即可，即 my_arduino_params.yaml 文件中的 wheel_track 参数。

** 校准策略 **：如果实际旋转角度大于指定角度，调大 wheel_track 参数，反之调小。

## 2.2 说明

完整代码：

https://github.com/deng-ke/DKbot/tree/master/dkbot_controller/dkbot_calibrate。

代码原理基本同线速度校准，核心计算也是不断计算旋转角度，判断是否达到指定角度。

## 2.3 测试

分别运行以下命令：

```shell
roslaunch ros_arduino_python arduino.launch
rosrun dkbot_calibrate calibrate_angular.py
rosrun rqt_reconfigure rqt_reconfigure
```

进入动态配置界面后，点击 start_test，DKbot 开始旋转，再根据 2.1 节校准策略校准。

![dkbot4_02](http://www.dengke.xyz/usr/uploads/2019/12/3987708106.png)

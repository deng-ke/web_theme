&#8195;&#8195;使用Eigen实现矩阵基本运算，以及位姿（旋转向量/矩阵、变换矩阵、欧拉角、四元数）表示。
***

# 1．Eigen库
&#8195;&#8195;官网：[http://eigen.tuxfamily.org][1]。
&#8195;&#8195;文档：[http://eigen.tuxfamily.org/dox/][2]。
&#8195;&#8195;快速参考：[http://eigen.tuxfamily.org/dox/group__QuickRefPage.html][3]。

# 2．Eigen基本操作
&#8195;&#8195;完整代码：
&#8195;&#8195;[https://github.com/deng-ke/slam-base/tree/master/EigenMatrix][4]。

## 2.1 模块和头文件

序号 | 模块 | 头文件 | 内容
-|-|-|-
1 | Core | #include<Eigen/Core> | Matrix/Array类、基础运算
2 | Geometry | #include<Eigen/Geometry> | 旋转/平移/缩放，2D/3D变换
3 | LU | #include<Eigen/LU> | 求逆，行列式，LU分解
4 | Cholesky | #include<Eigen/Cholesky> | LLT和LDLT Cholesky分解
5 | Householder | #include<Eigen/Householder> | Householder变换
6 | SVD | #include<Eigen/SVD> | SVD分解
7 | QR | #include<Eigen/QR> | QR分解
8 | Eigenvalues | #include<Eigen/Eigenvalues> | 特征值/特征向量分解
9 | Sparse | #include<Eigen/Sparse> | 稀疏矩阵的存储和运算
10 | Dense | #include<Eigen/Dense> | 包含了1-4、6-8头文件
11 | Eigen | #include<Eigen/Eigen> | Dense/Sparse头文件

## 2.2 Matrix

### 2.2.1 矩阵、向量定义

* **基本定义：**
&#8195;&#8195;矩阵、向量都是Matrix模板类的对象，主要使用前三个参数，剩下为默认值。
```C++
Eigen::Matrix<float, 2, 3> matrix_23;
Eigen::Matrix<float,3,1> vd_3d;
```

* **动态大小：**
```C++
Eigen::Matrix< double, Eigen::Dynamic, Eigen::Dynamic > matrix_dynamic1;
Eigen::Matrix< double, 1, Eigen::Dynamic > matrix_dynamic2;
```

* **简写定义：**
&#8195;&#8195;typedef了一些简写：
![3_1.jpg](http://www.dengke.xyz/usr/uploads/2019/06/3650682119.png)

```C++
Eigen::Vector3d v_3d;
Eigen::MatrixXd matrix_x;
```

### 2.2.2矩阵、向量初始化
* **直接初始化：**
```C++
matrix_23 << 1, 2, 3, 4, 5, 6;
```
* **初始化为0：**
```C++
Eigen::Matrix3d matrix_33 = Eigen::Matrix3d::Zero();
```
![3_2.jpg](http://www.dengke.xyz/usr/uploads/2019/06/3882983349.png)

### 2.2.3 访问矩阵中的元素
```C++
for (int i=0; i<2; i++) {
    for (int j=0; j<3; j++)
        cout<<matrix_23(i,j)<<"\t";
    cout<<endl;
}
```
![3_3.jpg](http://www.dengke.xyz/usr/uploads/2019/06/1619139418.png)

## 2.3运算
* **四则运算：**
以矩阵和向量相乘为例：

```C++
v_3d << 3, 2, 1;
vd_3d << 4,5,6;
Eigen::Matrix<float, 2, 1> result1 = matrix_23 * vd_3d;
cout << "matrix_23 * vd_3d = " << endl << result1 << endl;
Eigen::Matrix<double, 2, 1> result2 = matrix_23.cast<double>() * v_3d;
cout << "matrix_23 * v_3d = " << endl << result2 << endl;  //需显式转换类型
```
![3_4.jpg](http://www.dengke.xyz/usr/uploads/2019/06/2564844154.png)

* **其它运算：**
包括转置、逆、迹等：
```C++
cout << "转置：" << endl << matrix_33.transpose() << endl;
cout << "各元素和：" << endl << matrix_33.sum() << endl;
cout << "迹：" << endl << matrix_33.trace() << endl;
cout << "数乘：" << endl << 10*matrix_33 << endl;
cout << "逆：" << endl << matrix_33.inverse() << endl;
cout << "行列式：" << endl << matrix_33.determinant() << endl;
//实对称矩阵可保证对角化成功：
Eigen::SelfAdjointEigenSolver<Eigen::Matrix3d> eigen_solver ( matrix_33.transpose()*matrix_33 );
cout << "特征值和特征向量：" << endl;
cout << "Eigen values = \n" << eigen_solver.eigenvalues() << endl;
cout << "Eigen vectors = \n" << eigen_solver.eigenvectors() << endl;
```
![3_5.jpg](http://www.dengke.xyz/usr/uploads/2019/06/2160713250.png)

## 2.4 解方程
&#8195;&#8195;两种方法：直接求逆、利用QR分解。方法2更快。

* **直接求逆解方程：**
```C++
clock_t time_stt = clock();
Eigen::Matrix<double,MATRIX_SIZE,1> x = matrix_NN.inverse()*v_Nd;
cout << "直接求逆解方程：" << endl;
cout << "time use=" << endl << 1000* (clock() - time_stt)/(double)CLOCKS_PER_SEC << "ms"<< endl;
cout << "x=" << endl << x << endl;
```
![3_6.jpg](http://www.dengke.xyz/usr/uploads/2019/06/3125474669.png)

* **用QR分解解方程：**
```C++
time_stt = clock();
x = matrix_NN.colPivHouseholderQr().solve(v_Nd);
cout << "利用QR分解解方程：" << endl;
cout << "time use=" << endl << 1000*  (clock() - time_stt)/(double)CLOCKS_PER_SEC <<"ms" << endl;
cout << "x=" << endl << x << endl;
```
![3_7.jpg](http://www.dengke.xyz/usr/uploads/2019/06/2191331110.png)

# 3．Eigen实现位姿表示
&#8195;&#8195;完整代码：
&#8195;&#8195;[https://github.com/deng-ke/slam-base/tree/master/EigenGeometry][5]。
&#8195;&#8195;主要用到Geometry模块，该模块实现的类，包含旋转矩阵、变换矩阵、旋转向量、欧拉角、四元数等对位姿的描述方式。
![3_8.jpg](http://www.dengke.xyz/usr/uploads/2019/06/2387505068.png)

## 3.1 旋转向量/矩阵
&#8195;&#8195;AngleAxis定义旋转向量；Matrix3定义旋转矩阵。两种方式可将旋转向量转换为旋转矩阵。
```C++
/***************************************************************************
 *旋转向量/矩阵；旋转向量转换为旋转矩阵
 ***************************************************************************/
    Eigen::AngleAxisd rotation_vector ( M_PI/4, Eigen::Vector3d ( 0,0,1 ) );
    Eigen::Matrix3d rotation_matrix = Eigen::Matrix3d::Identity();
    Eigen::Vector3d v ( 1,0,0 );
    cout .precision(3);
    /*两种方式：旋转向量 to 旋转矩阵*/
    cout<<"rotation matrix =\n"<<rotation_vector.matrix() <<endl;
    rotation_matrix = rotation_vector.toRotationMatrix();
    /*使用旋转向量变换*/
    Eigen::Vector3d v_rotated = rotation_vector * v;
    cout<<"(1,0,0) after rotation = "<<v_rotated.transpose()<<endl;
    /*使用旋转矩阵变换*/
    v_rotated = rotation_matrix * v;
    cout<<"(1,0,0) after rotation = "<<v_rotated.transpose()<<endl<<endl;
```
![3_9.jpg](http://www.dengke.xyz/usr/uploads/2019/06/815129425.png)

## 3.2 变换矩阵

```C++
/***************************************************************************
 *变换矩阵
 ***************************************************************************/
    /*定义，并用旋转矩阵、平移向量进行初始化*/
    //T为4*4。用于一个旋转3维向量
    Eigen::Isometry3d T=Eigen::Isometry3d::Identity();
    T.rotate ( rotation_vector );
    T.pretranslate ( Eigen::Vector3d ( 1,3,4 ) );
    cout << "Transform matrix = \n" << T.matrix() <<endl;
    /*使用变换矩阵*/
    Eigen::Vector3d v_transformed = T*v;
    cout<<"v tranformed = "<<v_transformed.transpose()<<endl<<endl;
```
![3_10.jpg](http://www.dengke.xyz/usr/uploads/2019/06/2907151297.png)

## 3.3 欧拉角

```C++
/***************************************************************************
 *欧拉角；
 ***************************************************************************/
    Eigen::Vector3d euler_angles = rotation_matrix.eulerAngles ( 2,1,0 );
    cout<<"yaw pitch roll = "<<euler_angles.transpose()<<endl<<endl;
```
![3_11.jpg](http://www.dengke.xyz/usr/uploads/2019/06/1856515143.png)

## 3.4 四元数

```C++
/***************************************************************************
 *四元数
 ***************************************************************************/
    /*旋转矩阵转换为四元数*/
    Eigen::Quaterniond q = Eigen::Quaterniond ( rotation_vector );
    cout<<"quaternion = \n"<<q.coeffs() <<endl;
    /*旋转向量转换为四元数*/
    q = Eigen::Quaterniond ( rotation_matrix );
    cout<<"quaternion = \n"<<q.coeffs() <<endl;
    /*使用四元数*/
    v_rotated = q*v; //*经过了重载，原本：qvq-1
    cout<<"(1,0,0) after rotation = "<<v_rotated.transpose()<<endl;
```
![3_12.jpg](http://www.dengke.xyz/usr/uploads/2019/06/438882083.png)

  [1]: http://eigen.tuxfamily.org
  [2]: http://eigen.tuxfamily.org/dox/
  [3]: http://eigen.tuxfamily.org/dox/group__QuickRefPage.html
  [4]: https://github.com/deng-ke/slam-base/tree/master/EigenMatrix
  [5]: https://github.com/deng-ke/slam-base/tree/master/EigenGeometry
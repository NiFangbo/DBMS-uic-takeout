//Data Generator for UIC Campus Takeout
#include<bits/stdc++.h>
#define endl '\n'
using namespace std;

map<string,int> mp;

int num[12] = {31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31};
vector<string> DateTime(){
    int M = 1 + rand() % 11;
    int d = 1 + rand() % num[M - 1];
    string month = to_string(M);
    string date = to_string(d);
    if ( month.length() < 2 ) month = '0' + month;
    if ( date.length() < 2 ) date = '0' + date;
    
    int h = rand() % 23, m = rand() % 60, s = rand() % 60;
    int cm = 1 + rand() % 15, cs = rand() % 60;
    int ns1 = (s + cs) % 60, nm1 = (m + cm) % 60 + (s + cs) / 60, nh1 = h + (m + cm) / 60;
    int dm = 10 + rand() % 30, ds = rand() % 60;
    int ns2 = (ns1 + ds) % 60, nm2 = (nm1 + dm) % 60 + (ns1 + ds) / 60, nh2 = h + (nm1 + dm) / 60;

    vector<string> ans;
    string hour, minute, sec;
    hour = to_string(h), minute = to_string(m), sec = to_string(s);
    if ( hour.length() < 2 ) hour = '0' + hour;
    if ( minute.length() < 2 ) minute = '0' + minute;
    if ( sec.length() < 2 ) sec = '0' + sec;
    ans.push_back("2024-" + month + "-" + date + " " + hour + ":" + minute + ":" + sec);
    hour = to_string(nh1), minute = to_string(nm1), sec = to_string(ns1);
    if ( hour.length() < 2 ) hour = '0' + hour;
    if ( minute.length() < 2 ) minute = '0' + minute;
    if ( sec.length() < 2 ) sec = '0' + sec;
    ans.push_back("2024-" + month + "-" + date + " " + hour + ":" + minute + ":" + sec);
    hour = to_string(nh2), minute = to_string(nm2), sec = to_string(ns2);
    if ( hour.length() < 2 ) hour = '0' + hour;
    if ( minute.length() < 2 ) minute = '0' + minute;
    if ( sec.length() < 2 ) sec = '0' + sec;
    ans.push_back("2024-" + month + "-" + date + " " + hour + ":" + minute + ":" + sec);

    return ans;
}

signed main(){
    srand(time(0));
       
    // insert customer
    freopen("customer_data.sql", "w", stdout);
    cout << "use `uic_takeout`;" << endl;
    cout << "INSERT INTO `user` (`user_type`, `username`, `password`) VALUES" << endl;
    for ( int i = 1; i <= 50000; i++ ){
        string username = "";
        while ( username == "" || mp.count(username) ){
            username = "";
            for ( int j = 1; j <= 5 + rand() % 5; j++ ){
                username += (char)('a' + rand() % 26);
            }
        }
        mp[username]++;
        string password = "";
        for ( int j = 1; j <= 5 + rand() % 5; j++ ){
            password += (char)('a' + rand() % 26);
        }
        for ( int j = 1; j <= 3 + rand() % 3; j++ ){
            password += (char)('0' + rand() % 10);
        }
        cout << "('customer', '" << username << "', '" << password << "')";
        if ( i < 50000 ) cout << "," << endl;
        else cout << ";";
    }
    
    // insert deliveryman
    freopen("deliveryman_data.sql", "w", stdout);
    cout << "use `uic_takeout`;" << endl;
    cout << "INSERT INTO `user` (`user_type`, `username`, `password`) VALUES" << endl;
    for ( int i = 1; i <= 10000; i++ ){
        string username = "";
        while ( username == "" || mp.count(username) ){
            username = "";
            for ( int j = 1; j <= 5 + rand() % 5; j++ ){
                username += (char)('a' + rand() % 26);
            }
        }
        mp[username]++;
        string password = "";
        for ( int j = 1; j <= 5 + rand() % 5; j++ ){
            password += (char)('a' + rand() % 26);
        }
        for ( int j = 1; j <= 3 + rand() % 3; j++ ){
            password += (char)('0' + rand() % 10);
        }
        cout << "('deliveryman', '" << username << "', '" << password << "')";
        if ( i < 10000 ) cout << "," << endl;
        else cout << ";";
    }

    // insert order
    freopen("order_data.sql", "w", stdout);
    
    string delivery_status[4] = {"Pending", "Delivering", "Completed", "Cancelled"};
    int customer_order[50005];
    vector<int> completed_order;
    map<int,int> completed_id;

    cout << "use `uic_takeout`;" << endl;
    cout << "INSERT INTO `order`(`customer_id`, `order_time`, `order_address`, `deliveryman_id`, `check_time`, `delivery_status`, `complete_time`) VALUES" << endl;
    for ( int i = 1; i <= 50000; i++ ){
        string status = delivery_status[rand() % 4];
        int customer_id = 5 + rand() % 50000;
        vector<string> rndt = DateTime();
        string order_time = rndt[0];
        string order_address;
        if ( rand() % 2 ) order_address = "T" + to_string(4 + rand() % 5) + "-" + to_string(1 + rand() % 7) + "0" + to_string(1 + rand() % 7);
        else order_address = "V" + to_string(15 + rand() % 10);
        string deliveryman_id = "NULL", check_time = "NULL", complete_time = "NULL";
        if ( status != "Pending" ){
            deliveryman_id = to_string(50005 + rand() % 10000);
            check_time = rndt[1];
            if ( status != "Cancelled" ) complete_time = rndt[2];
        }
        if ( status == "Completed" ){
            completed_order.push_back(i);
            completed_id[i]++;
        }
        cout << "(" << customer_id << ", '" << order_time <<"', '" << order_address << "', " << deliveryman_id << ", '" << check_time << "', '" << status << "', '" << complete_time<< "')";
        if ( i < 50000 ) cout << "," << endl;
        else cout << ";" ;
        customer_order[i] = customer_id;
    }

    // insert transaction
    freopen("transaction.sql", "w", stdout);

    vector<int> order_product;

    cout << "use `uic_takeout`;" << endl;
    cout << "INSERT INTO `transaction` VALUES" << endl;
    for ( int i = 5; i <= 50004; i++ ){
        int pid = 1 + rand() % 35;
        double price = (double)(rand() % 100) / 100 + rand() % 50 + 1;
        int quantity = 1 + rand() % 20;
        order_product.push_back(pid);
        cout << "(" << i << ", " << pid << ", " << fixed << setprecision(2) << price << ", " << quantity << ")";
        if ( i < 50004 ) cout << "," << endl;
        else cout << ";";
    }

    // insert comment
    freopen("comment.sql", "w", stdout);

    string content_type[5] = {"The quality is not well enough.", " Good service.", "Good product!", "Great product!", "Excellent quality!"};

    cout << "use `uic_takeout`;" << endl;
    cout << "INSERT INTO `comment` VALUES" << endl;
    for ( int i = 1; i <= completed_order.size(); i++ ){
        int order_id = completed_order[i - 1];
        string comment_date = "2024-12-0" + to_string(1 + rand() % 9);
        int level = rand() % 5;
        string content = content_type[level];
        cout << "(" << i + 1 << ", " << order_id << ", '" << comment_date << "', '" << content << "', " << level + 1 << ", 'Shown')";
        if ( i < completed_order.size() ) cout << "," << endl;
        else cout << ";";   
    }

    // insert comment_product
    freopen("comment_product.sql", "w", stdout);
    cout << "use `uic_takeout`;" << endl;
    cout << "INSERT INTO `comment_product` VALUES" << endl;
    for ( int i = 1; i <= completed_order.size(); i++ ){
        cout << "(" << i + 1 << ", " << order_product[i - 1] << ")";
        if ( i < completed_order.size() ) cout << "," << endl;
        else cout << ";";
    }

    return 0;
}
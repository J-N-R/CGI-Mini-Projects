#include <boost/date_time/gregorian/gregorian.hpp>
#include <boost/algorithm/string/split.hpp>
#include <ctime>
#include <string>
#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <iomanip>

#include <cgicc/CgiDefs.h>
#include <cgicc/Cgicc.h>
#include <cgicc/HTTPHTMLHeader.h>
#include <cgicc/HTMLClasses.h>

#define OBJECT 'T'
#define BG 'B'

using namespace std;
using namespace cgicc;
using namespace boost::gregorian;
using namespace boost::algorithm;


struct Object {
   int centerX = 0;
   int centerY = 0;
   int size = 0;
   int id = 0;
};


// Recursive function that simulates paint brush algorithm, visits adjacent cells looking for objects
void visit(const int WIDTH, const int HEIGHT, char **arr, int x, int y, const char P, Object &obj) {  
   if(x >= 0 && x < WIDTH && y >= 0 && y < HEIGHT && arr[y][x]==P) {
      obj.size++;
      obj.centerX += x;
      obj.centerY += y;
      arr[y][x] = '0'+obj.id;
   visit(WIDTH, HEIGHT, arr, x, y-1, P, obj);
   visit(WIDTH, HEIGHT, arr, x-1, y, P, obj);
   visit(WIDTH, HEIGHT, arr, x, y+1, P, obj);
   visit(WIDTH, HEIGHT, arr, x+1, y, P, obj);
   }
}


// Validates a user's age given current date, user inputted birthday, and user inputted age
string validateAge(int year, int month, int day, string birthday, int age) {
   char *source = strdup(birthday.c_str());   
   
   int byear = stoi( strtok(source, "-"));
   int bmonth = stoi( strtok(NULL, "-"));
   int bday = stoi( strtok(NULL, "-"));

   int realAge = year - byear;
   if(month < bmonth || (month == bmonth && day < bday))
      realAge--;

   if(realAge < 0)
      return "\n   <br><font color=red>Invalid Age. Are you from the future?</font>";

   else if(age != realAge)
      return "\n   <br><font color=red>Your age should be " + to_string(realAge) + ", NOT " + to_string(age) + "</font>";

   else
      return "";
}


// function to save large string message to file. const& to avoid duplicate
void saveUploadFile(const string &msg, string filePath) {

   ofstream Outfile(filePath);
   Outfile << msg;
   Outfile.close();

}


// function to process input text data, can work with any input source
string checkTextData(string mydata, const string type, int &objCt) {
   char cstr[mydata.size()+1];
   char *token;
   Object myObj;
   ostringstream strX, strY; // Create Output Stream of String (self study needed)

   strX << fixed;
   strY << fixed;
   strX << setprecision(2);
   strY << setprecision(2);

   objCt = 0;
   mydata.copy(cstr, mydata.size()+1);
   
   string msg = "";
   
   token = strtok(cstr, "\n");  // split string into tokens, take height int and width

   int height = stoi(strtok(token, ","));
   int width = stoi(strtok(NULL, ","));

   if (height > 0 && width > 0) { // initialize character data array to use with visit()
      char **darray = new char *[height];

      for(int i=0; i<height; i++)
         darray[i] = new char[width];

      msg += "\n   <br>height: <b>" + to_string(height) + "</b>, width: <b>" + to_string(width) + "</b>";

      mydata.copy(cstr, mydata.size()+1);
      token = strtok(cstr, "\n");
      token = strtok(NULL, "\n");
      for (int i = 1; i <= height; i++) { // print out data before processing
         msg=msg+ "\n   <br>" + token;

         for (int j = 0; j < width; j++)
            strncpy(darray[i-1], token, width);

         token = strtok(NULL, "\n");
      }
      myObj.id = 0;

   // RASTER SCAN
   // scan every cell, and if object found, use visit recursive function to fully define object
      for(int i = 0; i < height; i++)
         for (int j = 0; j < width; j++)
            if (darray[i][j] == OBJECT) {
               myObj.centerX=0; myObj.centerY=0; myObj.size=0; myObj.id++;
               visit(width, height, darray, j, i, OBJECT, myObj);
               if (myObj.size > 0) { // if an object found, print info
                  strX.str("");
                  strY.str("");
                  strX.clear();
                  strY.clear();
                  strX << ((float)myObj.centerX/myObj.size);
                  strY << ((float)myObj.centerY/myObj.size);
                  
                  msg=msg+ "\n   <br>Object id <b>" + to_string(myObj.id) + "</b> starts at (<b>" + to_string(j) + "</b>,<b>" + to_string(i) + "</b>), size: <b>" + to_string(myObj.size) + "</b> chars, center at (<b>" + strX.str() + "</b>,<b>" + strY.str() + "</b>)";
               }
            }

      objCt = myObj.id;
      msg+= "\n   <br>Total number of objects: <b>" + to_string(objCt) + "</b>\n<pre>\n";
 
      for(int i=0; i<height; i++) { // print data after processing (object characters replaced with ids)
         for(int j = 0; j < width; j++) 
            msg+= darray[i][j];

         msg+= "\n";
      }
      msg+= "</pre>";
      
   }

   return msg;


}


int main() { // is this good coding style? put all variables at beginning? I should ask
    Cgicc cgi;

//  TIME VARIABLES
    std::time_t t = std::time(0);
    std::tm* now = std::localtime(&t);

    string year  = to_string(now->tm_year + 1900);
    string month = to_string(now->tm_mon + 1);
    string day   = to_string(now->tm_mday);

    string hour = to_string(now->tm_hour);
    string min  = to_string(now->tm_min);
    string sec  = to_string(now->tm_sec);
    
//  ENVIRONMENT VARIABLES
    CgiEnvironment env = cgi.getEnvironment();

    string host   = env.getRemoteHost();
    string ip     = env.getRemoteAddr();
    string browser = env.getUserAgent();

//  CGI VARIABLES (Helps keep output statements clean)
    string name = cgi("name");
    string gender = cgi("gender");

    int    age = std::stoi(cgi("age"));
    float  gpa = std::stof(cgi("GPA"));
    
    //string grad_date = cgi("date");
    string course = cgi("course");
    string birthday = cgi("birthday");
    string term = cgi("term");

    string mydata = cgi("mydata");

    bool biking = cgi.queryCheckbox("Biking");
    bool swimming = cgi.queryCheckbox("Swimming");
    bool reading  = cgi.queryCheckbox("Reading");
    bool fishing  = cgi.queryCheckbox("Fishing");

//  OUTPUT MSG (Formatted to look like HTML Output)
    string msg="";
    
    cout << "Content-type: text/html\n\n";

   
    msg=msg+ "\n<html>\n";

    msg=msg+ "\n <head> <title>Project 3 Output File by rivejona@kean.edu</title> </head>\n\n";
    

    msg=msg+ "\n <body>\n";

    msg=msg+ "\n <div style='font-size: 150%'>\n";

    msg=msg+ "\n   <h1>CPS 3525 Project 3 Output File</h1>\n";

    msg=msg+ "\n   Current date: <b>" + year + "-" + month + "-" + day + "</b>";
    msg=msg+ "\n   Current time: <b>" + hour + ":" + min + ":" + sec + "</b>";
    msg=msg+ "\n   <br>User information from the browser:";
    msg=msg+ "\n   <br>IP: <b>" + ip + "</b>";
    msg=msg+ "\n   <br>Browser/OS: " + browser;
    msg=msg+ "\n   <br>Name: <b>" + name + "</b>";
    msg=msg+ "\n   <br>Gender: <b>" + gender + "</b>";
    msg=msg+ "\n   <br>Age: <b>" + to_string(age) + "</b>";

//  Verify Age.
    if (age > 110 || age <= 0)
       msg+= "\n   <font color=red>Error! age should be >0 and <110.</font>";

    msg=msg+ "\n   <br>GPA: <b>" + to_string(gpa) + "</b>";


//  Verify GPA.
    if (gpa > 4 || gpa < 0)
       msg+= "\n   <font color=red>Error! gpa should be >0 and <=4.</font>";

    msg=msg+ "\n   <br>Birthday: <b>" + birthday + "</b>";

//  Verify Birthday.
    msg+= validateAge(stoi(year), stoi(month), stoi(day), birthday, age);

    msg=msg+ "\n   <br>Interests Selected:\n   <ul style='margin:0'>";


//  Output Interests
    if (biking)   msg=msg+ "\n     <li><b>Biking</b></li>";
    if (swimming) msg=msg+ "\n     <li><b>Swimming</b></li>";
    if (reading)  msg=msg+ "\n     <li><b>Reading</b></li>";
    if (fishing)  msg=msg+ "\n     <li><b>Fishing</b></li>";

    if(!biking && !swimming && !reading && !fishing)
       msg=msg+ "\n   <font color=red>No Interest was selected.</font>";


    msg=msg+ "\n   </ul>\n";
    
    msg=msg+ "\n   Term: <b>" + term + "</b>";
    msg=msg+ "\n   <hr>\n";

    msg=msg+ "\n   Input object text from the web:";
    msg=msg+ "\n   <br>";

    int fileObjCt = 0;
    int webObjCt  = 0;


//  VALIDATION COMPLETE. PROCESS WEB INPUT
    msg=msg+ "\n   " + checkTextData(mydata, "web input", webObjCt);


//  WEB INPUT COMPLETE, PROCESS FILE INPUT
    string fname = "project3out.html";
    string fpath = "../CGIMiniProjects/upload/" + fname;
    string filedata;

    const_file_iterator file = cgi.getFile("file");

    if(file != cgi.getFiles().end())
       filedata = file->getData(); 

    msg = msg+ "\n   <hr>";
    msg = msg+ "\n   The data from the uploaded file:";
    msg = msg+ checkTextData(filedata, "file input", fileObjCt);
    msg = msg+ "\n   <hr>\n   File input has " + to_string(fileObjCt) + " objects, and web input has " + to_string(webObjCt) + " objects.";    


//  File input complete, compare outputs
    if(fileObjCt > webObjCt)
       msg= msg+ "\n   File input has more objects.";
    
    else if (fileObjCt == webObjCt)
       msg= msg+ "\n   Web and File input have the same number of objects.";

    else
       msg= msg+ "\n   Web input has more objects.";

    msg=msg+ "\n </div>\n";

    msg=msg+ "\n </body>\n";

    msg=msg+ "\n</html>";

//  PROCESSING COMPLETE. SAVE OUTPUT TO FILE
    saveUploadFile(msg, fpath);

    cout << "\n<span style='font-size: 250%'>\n<br>File uploaded and saved successfully.";
    cout << "\n<hr style='height: 1.5%; background: black'>\n<br>";
    cout << "You can see the file and message <a href='" + fpath + "'>here.</a>\n</span>";

    return 0;
}

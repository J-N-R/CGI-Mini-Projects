#!/usr/local/bin/python3.8

import os
import cgi
import cgitb
import subprocess
from subprocess import STDOUT, PIPE
import stat
import smtplib
import datetime

# GOAL: Passed 2 File objects. Verify files, then save to uploads.
# Pass saved uploaded files to Java program
# Email professor

# Note: I'm not using helper function so error messages can be more specific
# Example: Your State File has incorrect columns


# Enable cgitb and set form
cgitb.enable()
form = cgi.FieldStorage()

print('Content-Type: text/html\n')

print('<head></head>')

print('<body>\n')

print('<div style = "margin-left: 2.5%; font-size: 200%">')

print('<h1 style="line-height: 150%; margin-bottom:-1.25%">Project 2 Results</h1>\n')

try:
    if 'Upload' not in form:
        raise Exception(f'Please Visit the <a href="../CGIMiniProjects/project2.html">previous page</a> first.')

    # Initialize important variables
    stateFile = form['stateFile']
    zipcodeFile = form['zipcodeFile']

    stateAllowedColumns = 8
    zipcodeAllowedColumns = 21

    basePath = './upload/'

    stateTarget = basePath + stateFile.filename
    zipcodeTarget = basePath + zipcodeFile.filename


    ### Verify State File ###
    state_line = stateFile.file.readline()
    tokens = state_line.split(b',')
    columns = len(tokens)
    fileExtension = os.path.splitext(stateFile.filename)[1]

    # If file is not csv, reject file. (Not part of requirement, but you don't want someone to upload JPG or something)
    if fileExtension != '.csv':
        raise Exception(f'<br>Incorrect file type. \n<br>State File: <b>{os.path.basename(stateFile.filename)}</b> must be .csv')
        

    # If file columns don't match, reject file
    if columns != stateAllowedColumns:
        raise Exception(f'<br>Incorrect amount of columns in State File: <b>{os.path.basename(stateFile.filename)}</b>\n<br>Allowed Columns: <b>{stateAllowedColumns}</b>. Columns in file: <b>{columns}</b>')
        


    ### Verify Zipcode File ###
    zipcode_line = zipcodeFile.file.readline()
    tokens = zipcode_line.split(b',')
    columns = len(tokens)
    fileExtension = os.path.splitext(zipcodeFile.filename)[1]

    # If file is not csv, reject file
    if fileExtension != '.csv':
        raise Exception(f'<br>Incorrect file type. \n<br>Zipcode File: <b>{os.path.basename(zipcodeFile.filename)}</b> must be .csv')

        

    # If file columns don't match, reject file
    if columns != zipcodeAllowedColumns:
        raise Exception(f'<br>Incorrect amount of columns in Zipcode File: <b>{os.path.basename(zipcodeFile.filename)}</b>\n<br>Allowed Columns: <b>{zipcodeAllowedColumns}</b>. Columns in file: <b>{columns}</b>')




    ### Upload Files ### Only upload if both are valid
    with open(stateTarget, 'wb') as targetFile:
        targetFile.write(state_line)
        targetFile.write(stateFile.file.read())
        os.chmod(stateTarget, stat.S_IRWXU | stat.S_IROTH | stat.S_IXOTH)


    with open(zipcodeTarget, 'wb') as targetFile:
        targetFile.write(zipcode_line)
        targetFile.write(zipcodeFile.file.read())
        os.chmod(zipcodeTarget, stat.S_IRWXU | stat.S_IROTH | stat.S_IXOTH)


    print('<p style="margin-left: 3.5%; line-height: 50%"> Files Successfully Uploaded.</p>\n')

    time = datetime.datetime.now()

    # Pass Files to java command
    cmd = "java -cp ./ Project2 \"" + str(stateTarget) + "\" \"" + str(zipcodeTarget) + "\""

    proc = subprocess.Popen(cmd, shell=True, stdout=PIPE, stderr=STDOUT, universal_newlines=True)

    stdout,stderr = proc.communicate(input='')

    if(stderr):
        raise Exception(f'Java Error has occured: {stderr}')
    else:
        print(stdout)
    
    timeDifference = ((datetime.datetime.now() - time).total_seconds()) * 1000

    

    # Send Email
    sender = 'rivejona@kean.edu'
    recievers = 'rivejona@kean.edu'
    
    message = """Subject: CPS3525 Project 2 Result
To: rivejona@kean.edu
From: rivejona@kean.edu
Content-Type: text/html; charset = \"UTF-8\"

The java program was run at <b>"""

    message = message + str(time) + "</b> stamp (estimated execution time: <b>" + str(timeDifference) + "</b> milliseconds), and the result is available to view <a href = \"yoda.kean.edu/~rivejona/CPS3525/Project2_results.html\"> here </a>"

    try:
        smtpObj = smtplib.SMTP('localhost')
        smtpObj.sendmail(sender, recievers, message)
    except SMTPException:
        print('<br>Error sending email.<br>')

    print("</div>\n</body>")

except Exception as Err:
    print('\n<br>An Error has occured. Please try again.<br>')
    print(Err)
    print("</div>\n</body>")

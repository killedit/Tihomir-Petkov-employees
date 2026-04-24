# 2026-04-23-Sirma-task

CSV data parser that identifies the pair of employees who have worked together on common projects for the longest period of time.


## Setup
```
git clone https://github.com/killedit/Tihomir-Petkov-employees.git
cd Tihomir-Petkov-employees
docker compose up -d --build
```

The back-end Laravel API should run at:</br>
`http://127.0.0.1:8088`

There is a csv file in the project directory `employees.csv` used for testing.

![Sirma task](/resources/images/2026-04-23-sirma-task-home.png)

<!--

TASK:
- Pair of employees who have worked together.
- Create an application that identifies the pair of employees who have worked together on common projects for the longest period of time.

Input data:

A CSV file with data in the following format:
EmpID, ProjectID, DateFrom, DateTo

Sample data:
143, 12, 2013-11-01, 2014-01-05
218, 10, 2012-05-16, NULL
143, 10, 2009-01-01, 2011-04-27
...

Sample output:
143, 218, 8

Specific requirements:
- DateTo can be NULL, equivalent to today
- The input data must be loaded to the program from a CSV file
- The task solution needs to be uploaded in github.com, repository name must be in format:
    {FirstName}-{LastName}-employees

Bonus points:
- Create an UI. The user picks up a file from the file system and, after selecting it, all common projects of the pair are displayed in datagrid with the following columns:
    Employee ID #1, Employee ID #2, Project ID, Days worked
- More than one date format to be supported, extra points will be given if all date formats are supported.

Delivery time:
- One day after receiving the task.
-->

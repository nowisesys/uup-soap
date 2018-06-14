/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

 /*
 * Employees.java
 *
 * Created: 2018-jun-14, 16:03:43
 * Author:  Anders Lövgren (Nowise Systems)
 */
package client;

import employees.generated.*;
import employees.*;
import java.util.List;

/**
 * Test driver for the employees service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
public class Employees {

    public static void main(String[] args) {
        EmployeesService service = new EmployeesService();
        EmployeesPortType proxy = service.getEmployeesPort();

        try {
            System.out.println("+++ Test simple listing of all users:");
            List<String> result = proxy.getIdentities();
            System.out.println("Employees (get->identities): " + result);
        } catch (Exception exception) {
            System.err.println(exception.getMessage());
        }

        try {
            System.out.println("+++ Lookup employees from username:");
            Employee result;

            result = proxy.lookupEmployee("olle");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(result));

            result = proxy.lookupEmployee("carl");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(result));

            result = proxy.lookupEmployee("adam");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(result));

            result = proxy.lookupEmployee("eva");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(result));

            Company company = result.getCompany();
            System.out.println("Employees (lookup->company): " + Serialize.ToString(company));

            Job job = result.getJob();
            System.out.println("Employees (lookup->job): " + Serialize.ToString(job));

            Employee boss = proxy.lookupEmployee(result.getBoss());
            System.out.println("Employees (lookup->boss): " + Serialize.ToString(boss));

            result = proxy.getBoss("eva");
            System.out.println("Employees (get->boss): " + Serialize.ToString(result));

            company = proxy.getCompany("eva");
            System.out.println("Employees (get->company): " + Serialize.ToString(company));

            job = proxy.getJob("eva");
            System.out.println("Employees (get->job): " + Serialize.ToString(job));
        } catch (Exception exception) {
            System.err.println(exception.getMessage());
        }

        try {
            System.out.println("+++ Find employee records by name:");
            List<Employee> result;

            result = proxy.findEmployee("Adam Svensson");
            for (Employee employee : result) {
                System.out.println("Employees (find->employee): " + Serialize.ToString(employee));
            }

            result = proxy.findEmployee("Eva Marklund");
            for (Employee employee : result) {
                System.out.println("Employees (find->employee): " + Serialize.ToString(employee));
            }
        } catch (Exception exception) {
            System.err.println(exception.getMessage());
        }

        try {
            System.out.println("+++ Dump all employees:");
            EmployeeList result;

            result = proxy.dumpEmployees();
        } catch (Exception exception) {
            System.err.println(exception.getMessage());
        }

        try {
            System.out.println("+++ Add, update and delete employee:");
            List<String> identities;

            identities = proxy.getIdentities();
            System.out.println("Employees (get->identities): " + identities);

            Employee employee = new Employee();
            employee.setCompany(new Company());
            employee.setJob(new Job());
            employee.setName("Anders Lövgren");
            employee.getCompany().setName("Nowise Systems");
            employee.getJob().setTitle("Programmer");
            proxy.addEmployee("anders", employee);

            identities = proxy.getIdentities();
            System.out.println("Employees (get->identities): " + identities);

            employee = proxy.lookupEmployee("anders");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(employee));

            employee.setName("Anders S Lövgren");
            employee.getCompany().setName("BMC-IT, Uppsala University");
            employee.getCompany().setAddress("Husargata 3, Uppsala");
            employee.getJob().setTitle("System Developer");

            proxy.updateEmployee("anders", employee);

            identities = proxy.getIdentities();
            System.out.println("Employees (get->identities): " + identities);

            employee = proxy.lookupEmployee("anders");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(employee));

            proxy.deleteEmployee("anders");

            identities = proxy.getIdentities();
            System.out.println("Employees (get->identities): " + identities);

            employee = proxy.lookupEmployee("anders");
            System.out.println("Employees (lookup->employee): " + Serialize.ToString(employee));
            
            boolean exists = proxy.hasEmployee("anders");
            System.out.println("Employees (has->employee): " + exists);
        } catch (Exception exception) {
            System.err.println(exception.getMessage());
        }
    }
}

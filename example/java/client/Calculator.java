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
 * Calculator.java
 *
 * Created: 2018-jun-14, 03:44:30
 * Author:  Anders Lövgren (Nowise Systems)
 */
package client;

import calculator.generated.*;

/**
 * Test driver for the calculator service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
public class Calculator {

    public static void main(String[] args) {
        CalculatorService service = new CalculatorService();
        CalculatorPortType proxy = service.getCalculatorPort();

        float a = 9.45f;
        float b = 5.63f;
        float result;

        try {

            result = proxy.add(a, b);
            System.out.println("Result (add): " + result);

            result = proxy.substract(a, b);
            System.out.println("Result (substract): " + result);

            result = proxy.divide(a, b);
            System.out.println("Result (divide): " + result);

            result = proxy.multiply(a, b);
            System.out.println("Result (multiply): " + result);

            result = proxy.modulo(a, b);
            System.out.println("Result (modulo): " + result);

            result = proxy.divide(a, 0);   // Should trigger exception from server
            System.out.println("Result (assert): " + result);

        } catch (Exception exception) {
            System.err.println(exception.getMessage());
        }
    }

}

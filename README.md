## UUP-SOAP - PHP classes as SOAP services.

Export your PHP classes as SOAP services with automatic service documentation gathered 
from annotations on class, methods and parameters and availible for service consumer thru 
wsdl:documentation tags.

### FEATURES:

* Uses the native SOAP extension (fast).
* Generates web service description (WSDL).
* Generates web service API documentation (HTML).

### HOMEPAGE:

Visit project homepage for more information and extended examples:

* https://nowise.se/oss/uup/soap/

### INTRODUCTION:

The exported service uses either the class name or an object. The latter is is prefered in
case some special initialization has to be done. The parameter processing (i.e. detect if
WSDL mode) can be simplified by using the SOAP request class:

```php
public function response($request, $service)
{
    $request->process($service);            // Request mode detected
}
```

Or more more detailed where the `$request` is either some custom class or the one supplied
by this library:

```php
public function response($request, $service)
{
    switch ($request->target) {
        case 'soap':
            $service->handleRequest();      // Handle SOAP request
            break;
        case 'wsdl':
            $service->sendDescription();    // Send WSDL
            break;
        case 'docs':
            $service->sendDocumentation();  // Send API doc
            break;
    }
}
```

### TESTING:

Example Java files can be found in the example/java/client directory. Generate SOAP proxy
using wsimport or download the full project from:

* https://nowise.se/oss/uup/soap/demo

To test service documentation (assume examples is accessable as uup-soap under i.e.
htdocs):

* http://localhost/uup-soap/server/employees.php?docs=syntax
* http://localhost/uup-soap/server/employees.php?docs=html
* http://localhost/uup-soap/server/employees.php?docs=text
* http://localhost/uup-soap/server/employees.php?docs=wsdl
* http://localhost/uup-soap/server/employees.php?docs=xslt

These shortcut (commonly used query strings) are also supported:

* http://localhost/uup-soap/server/employees.php?docs=1       # Same as ?docs=html
* http://localhost/uup-soap/server/employees.php?wsdl=1       # Same as ?docs=wsdl

The mode has been superseeded:

* http://localhost/uup-soap/server/employees.php?docs=code    # use ?docs=html instead

### ABOUT DERIVED WORK:

The WSDL generation uses the WSDL_Gen class (its author is unknown), but was republished
by [Martin Goldhahn](mailto:mgoldhahn@gmail.com) on Google Code. The license for php-wsdl-generator 
that bundles that class is Apache 2.0 (same as this package license). A number of improvements
has been done on that class (see file header).

##### Related links:

1. http://web.archive.org/web/* 
2. http://www.schlossnagle.org/~george/php/WSDL_Gen.tgz
3. http://code.google.com/p/php-wsdl-generator/downloads

The other code was taken from the OpenExam project (openexam.io), re-licensed from GPL2
as Apache 2.0

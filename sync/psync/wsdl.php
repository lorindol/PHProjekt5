<?php

//
// $Id: wsdl.php,v 1.1.2.1 2007/08/02 09:54:48 albrecht Exp $
//

// catch all suspicious output
error_reporting(0);
ini_set('display_errors', 'Off');
ob_start();

include_once('./config.inc.php');
include_once('./func.inc.php');
setErrorFile();

ob_end_clean();
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8" ?>

<!-- WSDL description of the PHProjekt web service
     APIs -->

<!-- Revision 2004-10-30 -->

<definitions name="PHProjekt"
             targetNamespace="urn:PHProjekt"
             xmlns:typens="urn:PHProjekt"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
             xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
             xmlns="http://schemas.xmlsoap.org/wsdl/">

  <!-- Type definitions -->

  <types>
    <xsd:schema xmlns="http://www.w3.org/2001/XMLSchema"
                targetNamespace="urn:PHProjekt">

      <xsd:complexType name="ItemAryElement">
        <xsd:complexContent>
          <xsd:restriction base="soapenc:Array">
             <xsd:attribute ref="soapenc:arrayType" wsdl:arrayType="xsd:string[]"/>
          </xsd:restriction>
        </xsd:complexContent>
      </xsd:complexType>

     <xsd:complexType name="ItemAryElementContainer">
        <xsd:all>
         <xsd:element name="Element" type="typens:ItemAryElement"/>
        </xsd:all>
      </xsd:complexType>

      <xsd:complexType name="ItemAry">
        <xsd:complexContent>
          <xsd:restriction base="soapenc:Array">
             <xsd:attribute ref="soapenc:arrayType" wsdl:arrayType="typens:ItemAryElementContainer[]"/>
          </xsd:restriction>
        </xsd:complexContent>
      </xsd:complexType>

      <xsd:simpleType name="SyncMode">
        <xsd:restriction base="xsd:string">
          <xsd:enumeration value="Both" />
          <xsd:enumeration value="ToPHProjekt" />
          <xsd:enumeration value="FromPHProjekt" />
        </xsd:restriction>
      </xsd:simpleType>
    </xsd:schema>
  </types>

  <!-- Message definitions -->

  <message name="SyncResponse">
    <part name="ReplySet" type="typens:ItemAry"/>
  </message>

  <message name="EmptyResponse"></message>

  <message name="SimpleRequest">
    <part name="Username" type="xsd:string"/>
    <part name="Password" type="xsd:string"/>
  </message>

  <message name="SyncRequest">
    <part name="ExchangeSet" type="typens:ItemAry"/>
    <part name="Username" type="xsd:string"/>
    <part name="Password" type="xsd:string"/>
    <part name="Syncdate" type="xsd:string"/>
    <part name="Delete" type="xsd:boolean"/>
    <part name="WithPrivate" type="xsd:boolean"/>
    <part name="SyncMode" type="typens:SyncMode" />
  </message>

  <message name="SyncResync">
    <part name="ExchangeSet" type="typens:ItemAry"/>
    <part name="Username" type="xsd:string"/>
    <part name="Password" type="xsd:string"/>
    <part name="SyncMode" type="typens:SyncMode" />
  </message>


  <!-- Port definitions -->

  <portType name="PHProjektPort">

    <operation name="SyncContacts">
      <input message="typens:SyncRequest"/>
      <output message="typens:SyncResponse"/>
    </operation>

    <operation name="SyncTodos">
      <input message="typens:SyncRequest"/>
      <output message="typens:SyncResponse"/>
    </operation>

    <operation name="SyncCalendar">
      <input message="typens:SyncRequest"/>
      <output message="typens:SyncResponse"/>
    </operation>

    <operation name="SyncNotes">
      <input message="typens:SyncRequest"/>
      <output message="typens:SyncResponse"/>
    </operation>

    <operation name="SyncResync">
       <input message="typens:SyncResync"/>
       <output message="typens:SyncResponse"/>
    </operation>

    <operation name="ListProjects">
       <input message="typens:SimpleRequest"/>
       <output message="typens:SyncResponse"/>
    </operation>

    <operation name="SetTimeCard">
       <input message="typens:SyncRequest"/>
       <output message="typens:EmptyResponse"/>
    </operation>

  </portType>


  <!-- Binding definitions -->

  <binding name="PHProjektBinding" type="typens:PHProjektPort">
    <soap:binding transport="http://schemas.xmlsoap.org/soap/http"
                  style="rpc" />

    <operation name="SyncContacts">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

    <operation name="SyncTodos">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

    <operation name="SyncCalendar">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

    <operation name="SyncNotes">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

    <operation name="SyncResync">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

    <operation name="ListProjects">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

    <operation name="SetTimeCard">
      <soap:operation soapAction="urn:PHProjektAction"/>
      <input>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
      <output>
        <soap:body use="encoded"
                   namespace="urn:PHProjekt"
                   encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </output>
    </operation>

  </binding>



  <!-- Endpoint definition -->
  <service name="PHProjektService">
    <port name="PHProjektPort" binding="typens:PHProjektBinding">
      <soap:address location="';

if ( (substr(PHP_VERSION,0,1) == 5) &&  (function_exists('get_loaded_extensions')) && (in_array('soap', get_loaded_extensions()) )) {
    $file='soap5.php';
} else {
 $file='soap4.php';
}

if ( (isset($__ENV['SERVER_PORT'])) && ($_ENV['SERVER_PORT'] != 80) ) {
    $port = sprintf(':%d', $__ENV['SERVER_PORT']);
} else {
    $port = '';
}

// $file .= '?start_debug=1&amp;debug_stop=1';
printf('%s://%s%s%s/%s',
       (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS'])==='on')) ? 'https' : 'http',
       $_SERVER['SERVER_NAME'],
       $port,
       dirname($_SERVER['SCRIPT_NAME']),
       $file);


echo '"/>
        </port>
    </service>
</definitions>';
exit();
?>

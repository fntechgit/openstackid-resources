<?php
/**
 * Copyright 2019 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

/**
 * Class OAuth2SummitOrdersApiTest
 */
final class OAuth2SummitOrdersApiTest extends ProtectedApiTest
{
    /**
     * @return mixed
     */
    public function testGetAllMyOrders(){
        $params = [

            'page'     => 1,
            'per_page' => 10,
            'order'    => '+number',
            'expand'   => 'tickets,tickets.owner,tickets.badge'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitOrdersApiController@getAllMyOrders",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $my_orders = json_decode($content);
        $this->assertTrue(!is_null($my_orders));
        return $my_orders;
    }

    /**
     * @return mixed
     */
    public function testGetAllMyTickets(){
        $params = [

            'page'     => 1,
            'per_page' => 10,
            'order'    => '+number',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitTicketApiController@getAllMyTickets",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $my_orders = json_decode($content);
        $this->assertTrue(!is_null($my_orders));
        return $my_orders;
    }

    public function testRefundOrderWithEmptyPayload($summit_id = 27, $order_id = 5){
        $params = [
            'id'       => $summit_id,
            'order_id' =>  $order_id
        ];

        $data = [];
        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitOrdersApiController@refundOrder",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(412);

    }

    public function testRefundTicketWithEmptyPayload($summit_id = 27, $ticket_id = 5){
        $params = [
            'id'       => $summit_id,
            'ticket_id' =>  $ticket_id
        ];

        $data = [];
        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitTicketApiController@refundTicket",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(412);

    }

    public function testRefundOrderWithoutPayload($summit_id = 27, $order_id = 5){
        $params = [
            'id'       => $summit_id,
            'order_id' =>  $order_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitOrdersApiController@refundOrder",
            $params,
            [],
            [],
            [],
            $headers
            //json_encode($data)
        );

        $this->assertResponseStatus(412);

    }
    /**
     * @return mixed
     */
    public function testUpdateMyOrder(){

        $params = [
            'order_id' =>  7
        ];

        $data = [
            'owner_company' => 'OpenStack',
            'billing_address_1' => 'Siempre Viva Av.',
            'extra_questions' => [
                ['question_id' => 1, 'answer' => 'test'],
            ]
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitOrdersApiController@updateMyOrder",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $order = json_decode($content);
        $this->assertTrue(!is_null($order));
        return $order;
    }

    /**
     * @return mixed
     */
    public function testUpdateOrder($summit_id = 27, $order_id =7){

        $params = [
            'id' => $summit_id,
            'order_id' =>  $order_id
        ];

        $data = [
            'owner_first_name' => 'Sebastian',
            'owner_last_name' => 'Sebastian',
            'owner_email' => 'smarcet@gmail.com',
            'owner_company' => 'OpenStack',
            'billing_address_1' => 'Siempre Viva Av.',
            'extra_questions' => [
                    ['question_id' => 3, 'answer' => '1'],
                    ['question_id' => 4, 'answer' => ''],
                    ['question_id' => 5, 'answer' => ''],
            ]
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitOrdersApiController@update",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $order = json_decode($content);
        $this->assertTrue(!is_null($order));
        return $order;
    }


    public function testReserveFailingPromoCode($summit_id = 4){
        $params = [
            'id' =>  $summit_id,
        ];

        $data = [
            "owner_email" => "smarcet@gmail.com",
            "owner_first_name" => "Sebastian",
            "owner_last_name" => "Marcet",
            "owner_company"=>"Pumant",
            "tickets" => [
                ["type_id"=>1, "promo_code"=>"test100"],
                ["type_id"=>1, "promo_code"=>"Test100"],
                ["type_id"=>1, "promo_code"=>"TesT100"],
            ]
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitOrdersApiController@reserve",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }

    public function testTicketAssignmentWithoutExtraQuestions(){
            $params = [
                'order_id'  =>  23,
                'ticket_id' =>  21,
            ];

            $data = [
                "attendee_email" => "sebastian.jose@gmail.com",
                "attendee_first_name" => "sebastian",
                "attendee_last_name" => 'marcet',
                "attendee_company" => "pumant",
            ];

            $headers = [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"        => "application/json"
            ];

            $response = $this->action(
                "PUT",
                "OAuth2SummitOrdersApiController@assignAttendee",
                $params,
                [],
                [],
                [],
                $headers,
                json_encode($data)
            );

            $content = $response->getContent();
            $this->assertResponseStatus(412);
    }

    /**
     * @param int $summit_id
     * @param string $hash
     */
    public function testCheckoutFailedCountryValidation($summit_id= 27, $hash = 'ab45277bd50ba2d3284e56f06f2710049e5950927ec304fb7f8ea36e43cea931'){

        $params = [
            'id' =>  $summit_id,
            'hash' => $hash
        ];

        $data = [
            'billing_address_1'         => 'test',
            'billing_address_2'         => 'test',
            'billing_address_zip_code'  => 'test',
            'billing_address_city'      => 'test',
            'billing_address_state'     => 'test',
            'billing_address_country'   => 'test',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitOrdersApiController@checkout",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
        $this->assertTrue(str_contains($content, "billing_address_country"));
    }

    /**
     * @param int $summit_id
     * @param string $hash
     */
    public function testGetTicketForEditionByOrderHash($summit_id = 1, $hash = 'eb758846226109a736c512a6e0d682bdc6a3af67a0ad316315158f49c5f8f7e9'){

        $params = [
            'id'   => $summit_id,
            'hash' => $hash
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitOrdersApiController@getMyTicketByOrderHash",
            $params,
            [],
            [],
            [],
            $headers,
           []
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $ticket = json_decode($content);
        $this->assertTrue(!is_null($ticket));
        return $ticket;
    }

    /**
     * @param int $summit_id
     * @param int $order_id
     * @param int $ticket_id
     * @return mixed
     */
    public function testUpdateTicket($summit_id = 1, $order_id = 23 , $ticket_id = 21){

        $params = [
            'id'        => $summit_id,
            'order_id'  =>  $order_id,
            'ticket_id' => $ticket_id,
        ];

        $data = [
            'attendee_first_name' => 'Jose Arturo',
            'attendee_last_name' => 'Campanella',
            'attendee_email' => 'jcampanella@gmail.com',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitOrdersApiController@updateTicket",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $ticket = json_decode($content);
        $this->assertTrue(!is_null($ticket));
        return $ticket;
    }

    /**
     * @return mixed
     */
    public function testRevokeAttendee(){

        $params = [
            'order_id' =>  6,
            'ticket_id' => 18925,
        ];


        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitOrdersApiController@removeAttendee",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $ticket = json_decode($content);
        $this->assertTrue(!is_null($ticket));
        return $ticket;
    }

    public function testRegenerateTicketHash(){

        $params = [
            'hash' => '25f6d007523cc64b52fc513c49176119ef431f955eb8ace8cd3cdc959ab77893',
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitOrdersApiController@regenerateTicketHash",
            $params,
            [],
            [],
            []
        );

        $this->assertResponseStatus(200);
    }

    public function testGetTicketByHash(){

        $params = [
            'hash' => '87fb1166e8c41cfb4457dc1f5d11413549aeca833691bbbd6563f6335a948562',
            'expand' => 'owner,order,applied_taxes, applied_taxes.tax'
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitOrdersApiController@getTicketByHash",
            $params,
            [],
            [],
            []
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $ticket = json_decode($content);
        $this->assertTrue(!is_null($ticket));
        return $ticket;
    }

    public function testCreateSingleTicketOrder(){
        $params = [
            'summit_id' =>  27
        ];

        $data = [
            'owner_first_name' => 'Sebastian',
            'owner_last_name' => 'Marcet',
            'owner_email' => 'sebastian@marcet.com.ar',
            'ticket_type_id' => 108,
            "owner_company" => "Pumant",
            //'promo_code' => 'STAFF'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitOrdersApiController@add",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $order = json_decode($content);
        $this->assertTrue(!is_null($order));
        return $order;
    }

    /**
     * @param int $summit_id
     * @param int $order_id
     */
    public function testDeleteOrder($summit_id=27, $order_id = 6){
        $params = [
            'summit_id' =>  $summit_id,
            'order_id' => $order_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitOrdersApiController@delete",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetAllOrders($summit_id=3){
        $params = [
            'summit_id' => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+id',
            'expand'   => 'tickets,tickets.owner',
            //'filter'   => 'status<>Cancelled,status<>Reserved',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitOrdersApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $orders = json_decode($content);
        $this->assertTrue(!is_null($orders));
        return $orders;
    }

    public function testGetTicketPdfByID($ticket_id=21){
        $params = [
            'ticket_id' => $ticket_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitOrdersApiController@getTicketPDFById",
            $params,
            [],
            [],
            [],
            $headers
        );

        $pdf_content = $response->getContent();
        $this->assertResponseStatus(200);
        $this->assertTrue(!is_null($pdf_content));
        return $pdf_content;
    }

    public function testUpdateTicketById($ticket_id = 28){
        $params = [
            '$ticket_id' =>  $ticket_id
        ];

        $data = [
            'owner_first_name'    => 'Sebastian',
            'owner_last_name'     => 'Marcet',
            'owner_email'         => 'sebastian@marcet.com.ar',
            'disclaimer_accepted' => true,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitOrdersApiController@updateTicketById",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $order = json_decode($content);
        $this->assertTrue(!is_null($order));
        return $order;
    }
}
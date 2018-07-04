<?php

namespace Drupal\external_auth_module\Controller;

use Drupal\user\Controller\UserAuthenticationController;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class AuthController.
 */
class AuthController extends UserAuthenticationController {

  /**
   * Login.
   *
   * @return string
   *   Return Hello string.
   */
  public function login(Request $request) {
    try {
      $client = new Client([
        'base_uri' => 'http://localhost/drupal_share_db/'
      ]);

      # proccess to get the token, needed to perform any operation

      $response = $client->get('services/session/token');

      $token = $response->getBody()->getContents();

      unset($response);

      # proccess to login into drupal 7 site
      $data = [
        'username' => $request->request->get('name'),
        'password' => $request->request->get('pass')
      ];

      $headers = ['X-CSRF-TOKEN' => $token];

      $response = $client->post('api/user/login', [
        'json' => $data,
        'headers' => $headers
      ]);

      $data = json_decode($response->getBody()->getContents(), true);

      if($data['token'] !== null) {
        $markup = "Everything ok";

        parent::login($request);
      }

    }
    catch(RequestException $ex) {
      if($ex->hasResponse()) {
        $response = $ex->getResponse();

        if($response->getStatusCode() == 401) {
          $markup = "Verifique los datos ingresados. Compruebe que su usuario existe";
        }
        else if(
          in_array( $response->getStatusCode(), $this->errorCodes )
        ) {
          $markup = $response->getReasonPhrase();
        }
      }
    }

    return [
      '#type' => 'markup',
      '#markup' => $markup
    ];
  }
  /**
   * Logout.
   *
   * @return string
   *   Return Hello string.
   */
  public function logout() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: logout')
    ];
  }
  /**
   * Register.
   *
   * @return string
   *   Return Hello string.
   */
  public function register() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: register')
    ];
  }

}

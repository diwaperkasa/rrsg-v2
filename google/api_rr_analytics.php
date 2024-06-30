<?php

function analytics_setup_and_filter_ids_top_pageview($count = null)
{
  if (!class_exists('Google_Service_Analytics'))
    return;

  $profile_id = '106911288'; // Robb Report Analytic Google Profile ID 
  if (empty($profile_id))
    return;

  $client = new Google_Client();

  try {
    $KEY_FILE_LOCATION = __DIR__ . '/robb-report-sg-ga-a3c5a2018082.json';
    $client = new Google_Client();
    $client->setApplicationName("Robb Report SG Analytics Reporting");
    $client->setAuthConfig($KEY_FILE_LOCATION);
    $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    $analytics = new Google_Service_Analytics($client);
    $results   = $analytics->data_ga->get(
      'ga:' . $profile_id,
      date('Y-m-d', strtotime('-8 days')),
      date('Y-m-d', current_time('timestamp')),
      'ga:avgTimeOnPage',
      array(
        'dimensions'  => 'ga:pagePath,ga:pageTitle',
        'metrics'     => 'ga:pageviews,ga:uniquePageviews',
        'sort'        => '-ga:pageviews',
        'max-results' => $count, // 30-200
        'start-index' => 1,
      )
    );
    if (empty($results))
      return;

    $rows = $results->getRows();

    $post_ids = array();
    foreach ($rows as $row) {
      // if( count($post_ids) >= $count )
      //   break 1;

      $path = explode('/', $row[0]);
      $path = array_filter($path);
      $path = array_values($path);
      if (empty($path))
        continue;

      $slug = array_pop($path);

      $args = array(
        'post_type'      => array('post', 'features', 'package'),
        'posts_per_page' => -1,
        'name'           => sanitize_title($slug),
        'fields'         => 'ids',
        'date_query' => array(
          array(
            'after' => '1 week ago',
          ),
        )
      );
      $posts = get_posts($args);
      if (empty($posts))
        continue;

      $post_ids[] = $posts[0];
    }

    return $post_ids;
  } catch (Google_ServiceException $e) {
    $error = 'Google API Error code :' . $e->getCode() . "\n";
    $error .= 'Google API Error message: ' . $e->getMessage() . "\n";
    error_log($error);
  } catch (Google_Exception $e) {
    error_log('An error occurred: (' . $e->getCode() . ') ' . $e->getMessage() . "\n");
  }
}

function rr_fetch_post_ids_popular()
{
  $transient_name = '_robbreport_most_popular_post_ids';
  $post_ids       = get_transient($transient_name);

  if (false === $post_ids) {
    $post_ids = analytics_setup_and_filter_ids_top_pageview(250);
    set_transient($transient_name, $post_ids, HOUR_IN_SECONDS);
  }

  if (empty($post_ids)) {
    return;
  }
  
  return $post_ids;
}

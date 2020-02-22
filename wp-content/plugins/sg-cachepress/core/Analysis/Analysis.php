<?php
namespace SiteGround_Optimizer\Analysis;
use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Helper\Helper;

/**
 * SG Analysis main plugin class
 */
class Analysis {
	/**
	 * Disable specific optimizations for a blog.
	 *
	 * @since  5.4.0
	 *
	 * @param  array $result Speed test results.
	 */
	public function process_analysis( $result ) {

		// Bail if the are no results.
		if ( empty( $result ) ) {
			wp_send_json_error();
		}

		$messages = $this->get_optimization_messages();

		$items = array();

		foreach ( $result['lighthouseResult']['categories'] as $group ) {
			foreach ( $group['auditRefs'] as $ref ) {

				if ( empty( $ref['group'] ) ) {
					continue;
				}

				// Do not show render blocking message if we have top score.
				if (
					'render-blocking-resources' === $ref['id'] &&
					1.00 === $result['lighthouseResult']['categories']['performance']['score']
				) {
					continue;
				}

				$audit = $result['lighthouseResult']['audits'][ $ref['id'] ];

				if ( in_array( $ref['group'], array( 'load-opportunities', 'diagnostics' ) ) ) {
					if ( array_key_exists( $audit['id'], $messages ) ) {
						$audit['action'] = $messages[ $audit['id'] ];
					}

					switch ( $audit['scoreDisplayMode'] ) {
						case 'manual':
						case 'notApplicable':
							$items['passed']['data'][] = $audit;
							break;
						case 'numeric':
						case 'binary':
						default:
							if ( $audit['score'] >= 0.9 ) {
								$items['passed']['data'][] = $audit;
							} else {
								$items[ $ref['group'] ]['info'] = $result['lighthouseResult']['categoryGroups'][ $ref['group'] ];
								$items[ $ref['group'] ]['data'][] = $audit;
							}
							break;
					}
				} else {
					$items[ $ref['group'] ]['info'] = $result['lighthouseResult']['categoryGroups'][ $ref['group'] ];
					$items[ $ref['group'] ]['data'][] = $audit;
				}
			}
		}

		unset( $items['budgets'] );
		unset( $items['diagnostics'] );
		unset( $items['metrics'] );

		$items['score'] = $result['lighthouseResult']['categories']['performance']['score'];

		// Return the response.
		return $items;
	}

	/**
	 * Get optimization messages.
	 *
	 * @since  5.4.0
	 *
	 * @return array Custom analysis messages.
	 */
	public function get_optimization_messages() {
		$messages = array(
			'render-blocking-resources'  => array(
				'enabled'  => array(
					'siteground_optimizer_optimize_javascript_async',
				),
				'messages' => array(
					'enabled' => __( 'Not all resources can be deferred, so you may continue to get this message, even after your site is well optimized.', 'sg-cachepress' ),
					'default' => __( 'Enable the <strong>Defer Render-blocking JS</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> and exclude critical scripts from it to pass this audit. Note, that not all resources can be deferred, so you may continue to get this message, even after your site is well optimized.', 'sg-cachepress' ),
				),
			),
			'uses-responsive-images'     => array(
				'enabled'  => array(
					'siteground_optimizer_optimize_images',
				),
				'messages' => array(
					'enabled' => __( 'Check your theme and if you\'re not using images larger than the positions they fit. If you\'ve recently switched between themes, try regenerating your thumbnails too.', 'sg-cachepress' ),
					'default' => __( 'Make sure you’re not loading the original images but a properly sized thumbnail. In addition, you can enable the check the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#images">Image Optimization tab</a> to optimize new uploads and bulk optimize existing images in your site.', 'sg-cachepress' ),
				),
			),
			'offscreen-images'           => array(
				'enabled' => array(
					'siteground_optimizer_lazyload_images',
				),
				'messages' => array(
					'enabled' => __( 'Not all images can be lazy-loaded, so you may continue to get this message, even after your site is well optimized.', 'sg-cachepress' ),
					'default' => __( 'To pass this check, go to the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#images">Image Optimization tab</a> and enable the <strong>Lazy Load Images</strong> option.', 'sg-cachepress' ),
				),
			),
			'unminified-css'             => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the <strong>Minify CSS Files</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.Note, that you may keep getting this message because of the way your theme is structured.', 'sg-cachepress' ),
				),
			),
			'unminified-javascript'      => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the <strong>Minify JavaScript Files</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.', 'sg-cachepress' ),
				),
			),
			'unused-css-rules'           => array(
				'enabled' => array(
					'siteground_optimizer_optimize_css',
				),
				'messages' => array(
					'enabled' => __( 'Even if your CSS is minified, you may still get this report due to the way your theme is structured.', 'sg-cachepress' ),
					'default' => __( 'Enable the <strong>Minify CSS Files</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.Note, that you may keep getting this message because of the way your theme is structured.', 'sg-cachepress' ),
				),
			),
			'uses-optimized-images'      => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable <strong>New Imags Optimization</strong> and <strong>Existing Image Optimization</strong> options under the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#images">Image Optimization tab</a>.', 'sg-cachepress' ),
				),
			),
			'uses-webp-images'           => array(
				'messages' => array(
					'default' => Helper::is_avalon() ? __( 'Enable the <strong>WebP Support</strong> option under the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#images">Image Optimization tab</a>.', 'sg-cachepress' ) : __( 'WebP support will be available once we migrate your account to <a class="sg-link sg-with-color sg-typography sg-typography--break-all" target="_blank" href="https://www.siteground.com/blog/new-client-area-and-site-tools/">Site Tools</a>.', 'sg-cachepress' ),
				),
			),
			'uses-text-compression'      => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the <strong>GZIP Compression</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#environment">Environment Optimization tab</a>.', 'sg-cachepress' ),
				),
			),
			'uses-rel-preconnect'        => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'When loading 3rd party resources, use the preconnect parameter to inform your browser that this is an important script: <link rel="preconnect" \'href="https://example.com">', 'sg-cachepress' ),
				),
			),
			'time-to-first-byte'         => array(
				'enabled' => array(
					'siteground_optimizer_enable_cache',
				),
				'messages' => array(
					'enabled' => __( 'Check if you have the Browser-Specific Caching enabled, if so, retry the test to make sure you\'re testing cached results.', 'sg-cachepress' ),
					'default' => __( 'Enable the <strong>Dynamic Caching</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#supercacher">SuperCacher Settings</a> tab.', 'sg-cachepress' ),
				),
			),
			'redirects'                  => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Make sure that you don\'t "chain" multiple redirects from one page to another. Use only www or non-www version of your website depending on your preferences.', 'sg-cachepress' ),
				),
			),
			'uses-rel-preload'           => array(
				'enabled' => array(
					'siteground_optimizer_optimize_javascript_async',
				),
				'messages' => array(
					'enabled' => __( 'Not all resources can be deferred, so you may continue to get this message, even after your site is well optimized.', 'sg-cachepress' ),
					'default' => __( 'Enable the <strong>Defer Render-blocking JS</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> and exclude critical scripts from it to pass this audit.', 'sg-cachepress' ),
				),
			),
			'efficient-animated-content' => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'If you\'re using big animated GIFs on your site, try replacing them with actual videos which will provide better user experience and faster load times.', 'sg-cachepress' ),
				),
			),
			'total-byte-weight'          => array(
				'enabled' => array(
					'siteground_optimizer_optimize_html',
					'siteground_optimizer_optimize_javascript',
					'siteground_optimizer_combine_css',
				),
				'messages' => array(
					'enabled' => __( 'Check if you have the Browser-Specific Caching enabled, if so, retry the test to make sure you\'re testing cached results.', 'sg-cachepress' ),
					'default' => __( 'Enable the <strong>Minify the HTML Output</strong>, <strong>Minify JavaScript Files</strong> and <strong>Minify CSS Files</strong> options in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.', 'sg-cachepress' ),
				),
			),
			'uses-long-cache-ttl'        => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the </strong>Browser Caching</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#environment">Environment Optimization tab</a>.', 'sg-cachepress' ),
				),
			),
			'dom-size'                   => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the </strong>GZIP Compression</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#environment">Environment Optimization tab</a>. In addition, consider reducing the size and amount of content in your page.', 'sg-cachepress' ),
				),
			),
			'user-timings'               => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the <strong>Minify JavaScript Files</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.', 'sg-cachepress' ),
				),
			),
			'bootup-time'                => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the <strong>Minify JavaScript Files</strong> option in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.', 'sg-cachepress' ),
				),
			),
			'mainthread-work-breakdown'  => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Enable the <strong>Minify JavaScript Files</strong> and Defer Render-blocking JS options in the <a class="sg-link sg-with-color sg-typography sg-typography--break-all" href="#frontend">Frontend Optimization tab</a> to pass this audit.', 'sg-cachepress' ),
				),
			),
			'third-party-summary'        => array(
				'enabled' => array(),
				'messages' => array(
					'default' => __( 'Check for services like analytics tools, advertisement networks and tracking scrits and similar third party resources loaded outside of your site. Too many such scripts loaded may slow down your site signifficantly.', 'sg-cachepress' ),
				),
			),
		);

		$response_messages = array();

		foreach ( $messages as $type => $message ) {
			if ( empty( $message['enabled'] ) ) {
				$response_messages[ $type ] = $message['messages']['default'];
				continue;
			}

			$failed_check = 0;
			foreach ( $message['enabled'] as $option_name ) {
				if ( ! Options::is_enabled( $option_name ) ) {
					$failed_check++;
				}
			}

			if ( 0 === $failed_check ) {
				$response_messages[ $type ] = $message['messages']['enabled'];
				continue;
			}

			$response_messages[ $type ] = $message['messages']['default'];
		}

		return $response_messages;
	}

	public function run_analysis_rest( $url, $device = 'desktop' ) {
		$analysis = $this->run_analysis( $url, $device );


		if ( ! empty( $analysis['passed'] ) ) {
			$analysis['passed']['info'] = array(
				'title'       => __( 'The Following Areas of Your Site Are Well Optimized:', 'sg-cachepress' ),
				'id'          => 'passed',
			);
		}

		if ( ! empty( $analysis['load-opportunities'] ) ) {
			$analysis['load-opportunities']['info'] = array(
				'title'       => __( 'Opportunities to Optimize', 'sg-cachepress' ),
				'id'          => 'opportunities',
			);
		}

		$score = $analysis['score'];
		unset( $analysis['score'] );

		$response = array_merge(
			$this->get_messages( $score ),
			array(
				'data'      => $analysis,
				'timeStamp' => time(),
			)
		);

		return $response;
	}

	/**
	 * Return predefined response messages.
	 *
	 * @since  5.4.0
	 *
	 * @param  int $score The score returned from Google.
	 *
	 * @return array      Messages.
	 */
	public function get_messages( $score ) {
		$score = round( $score * 100 );

		if ( $score < 90 && $score > 49 ) {
			return array(
				'score' => $score,
				'class_name' => 'placeholder-without-svg placeholder-meduim',
				'title'      => __( 'Almost there!', 'sg-cachepress' ),
				'message'    => __( 'There are few more steps to achieve excellent results!', 'sg-cachepress' ),
			);
		}

		if ( $score < 49 ) {
			return array(
				'score' => $score,
				'class_name' => 'placeholder-without-svg placeholder-low',
				'title'      => __( 'More optimization needed!', 'sg-cachepress' ),
				'message'    => __( 'Your site is not performing in the best possible way, check out the optimization suggestions below.', 'sg-cachepress' ),
			);
		}

		return array(
			'score'      => $score,
			'class_name' => 'placeholder-without-svg placeholder-top',
			'title'      => __( 'Awesome! You have worked hard.', 'sg-cachepress' ),
			'message'    => __( 'Your site is loading super fast!', 'sg-cachepress' ),
		);
	}

	/**
	 * Get the page speed results from Google API.
	 *
	 * @since  5.4.0
	 *
	 * @param  string  $url     The URL to test.
	 * @param  string  $device  The device type.
	 * @param  integer $counter Added to retry 3 times if the request fails.
	 *
	 * @return array            The analisys result.
	 */
	public function run_analysis( $url, $device = 'desktop', $counter = 0 ) {
		// Try to get the analysis 3 times and then bail.
		if ( 3 === $counter ) {
			wp_send_json_error();
		}

		$full_url = home_url( '/' ) . trim( $url, '/' );

		// Hit the url, so it can be cached, when Google Api make the request.
		wp_remote_get( $full );

		// Make the request.
		$response = wp_remote_get(
			'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . $full_url . '&locale=' . get_locale() . '&strategy=' . $device,
			array(
				'timeout' => 15,
			)
		);

		// Make another request if the previous fail.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$counter++;
			return $this->run_analysis( $url, $device, $counter );
		}

		// Decode the response.
		$response = json_decode( $response['body'], true );

		// Return the analysis.
		return $this->process_analysis( $response );
	}
}

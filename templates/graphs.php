<script type="text/javascript">
    function lti_switch_tabs(tabname) {
        jQuery('.lti-tab').hide();
        jQuery('.nav-tab').removeClass('active');
        jQuery('#lti-tab-' + tabname).show();
        jQuery('#nav-tab-' + tabname).addClass('active');
    }

    <?php if(isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'settings') : ?>
    jQuery(document).ready(function(){
        lti_switch_tabs('settings');
    });
    <?php endif; ?>

</script>

<!-- so i can restyle default values -->
<div class="insights-pg">
  <div id="header">
    <div class="logo-title">
      <img id="logo" src="<?php echo LTI_ASSETS_URL; ?>img/lti_logo.png"/>
    </div>
    <div class="insights-desc">
      <p>Laiser Tag Insights is an extension of the Laiser Tag plugin that adds Google Analytics tracking and functionality to your tags. It includes the ability to track top tags by impressions and by clicks, and cross-references the search terms used to reach your website.
      </p>
        <p>Through advanced linguistic analysis and structured, intelligent tagging, you can achieve better website performance and be seen in relevant organic searches by your audience more often. </p>
    </div>
    <div class="insights-extras">
      <div class="insights-update">
        <p><strong><a href="http://www.laisertag.com" target="_blank">Visit our website</a></strong> for current tag trends, high performing tag data, and additional plugins from our Laiser Tag Suite.</p>
      </div>
    </div>
  </div>

  <div class="nav-tab-parent">
    <div class="nav-tab-wrapper">
        <a href="#" id="nav-tab-graphs" class="nav-tab active" onclick="lti_switch_tabs('graphs')">Content Graphs</a>
        <a href="#" id="nav-tab-metrics" class="nav-tab" onclick="lti_switch_tabs('metrics')">Article Metrics</a>
        <a href="#" id="nav-tab-settings" class="nav-tab" onclick="lti_switch_tabs('settings')">Settings</a>
    </div>
    <div class="postbox lti-tab insights-blue-border" id="lti-tab-graphs">

      <script type="text/javascript">
              jQuery(function () {

                  function plotWithOptions(divname, data) {
                      var options = {
                          legend: {position: "nw"},
                          xaxis: {mode: "time", minTickSize: [1, "day"], timezone: "browser"},
                          yaxis: {tickDecimals: 0},
                          points: {show: true},
                          lines: {show: true},
                          grid: {hoverable: true},
                          tooltip: {show: true, content: "%s: %y"}
                      };

                      var percentformat = function (val, axis) {
                          return val + "%"
                      };

                      if (divname === 'aggregatemetricsctr' || divname === "tagclickpercentage" || divname === "tagviewpercentage") {
                          options.yaxis = {max: 100, min: 0, tickSize: 10, tickFormatter: percentformat}
                      }

                      if (divname === "tagclickpercentage" || divname === "tagviewpercentage") {
                          options.colors = ['#afd8f8'];
                      }
                      jQuery.plot("#lti-ltbb-" + divname, data, options);
                  }

                  function load_all() {
                      var daterange = jQuery('#lti-ltbb-range').val();
                      jQuery(".lti-ltbb-holder").hide();
                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_tags_by_views',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var list = "<h3>Top Tags By Views</h3><p>(Daily Top 500 Pages)</p><ul class='tags-by-clicks'>";
                              jQuery.each(response.data.tags, function (idx, val) {
                                  list += "<li><div class='insight-tagnumber'>" + val.views + "</div><div class='insight-tagborder'>" + val.tag_string + "</div></li>";
                              });
                              list += "</ul>";
                              jQuery("#lti-ltbb-tagsbyviews").html(list);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });

                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_tags_by_clicks',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var list = "<h3>Top Tags By Clicks</h3> <p>(Daily Top 500 Pages)</p><ul class='tags-by-clicks'>";
                              jQuery.each(response.data.tags, function (idx, val) {
                                  list += "<li><div class='insight-tagnumber'>" + val.clicks + "</div><div class='insight-tagborder'>" + val.tag_string + "</div></li>";
                              });
                              list += "</ul>";
                              jQuery("#lti-ltbb-tagsbyclicks").html(list);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });

                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_aggregate_metrics',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var views = [
                                  {
                                      label: 'Views',
                                      data: response.data.dates.views
                                  },
                                  {
                                      label: 'Tag Views',
                                      data: response.data.dates.tag_views
                                  }
                              ];
                              var clicks = [
                                  {
                                      label: 'Clicks',
                                      data: response.data.dates.clicks
                                  },
                                  {
                                      label: 'Tag Clicks',
                                      data: response.data.dates.tag_clicks
                                  }
                              ];
                              var ctr = [
                                  {
                                      label: 'CTR (Top 500 Pages)',
                                      data: response.data.dates.avg_ctr
                                  },
                                  {
                                      label: 'Tag CTR (Top 500 Pages)',
                                      data: response.data.dates.tag_avg_ctr
                                  }
                              ];
                              var pos = [
                                  {
                                      label: 'Average Position (Top 500 Pages)',
                                      data: response.data.dates.avg_pos
                                  },
                                  {
                                      label: 'Tag Position (Top 500 Pages)',
                                      data: response.data.dates.tag_avg_pos
                                  }
                              ];
                              plotWithOptions('aggregatemetricsviews', views);
                              plotWithOptions('aggregatemetricsclicks', clicks);
                              plotWithOptions('aggregatemetricspos', pos);
                              plotWithOptions('aggregatemetricsctr', ctr);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });

                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_pages_at_position',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var pages = [];
                              jQuery.each(response.data.data, function (idx, val) {
                                  var newplot = {
                                      label: idx,
                                      data: val
                                  };
                                  pages.push(newplot);
                              });
                              plotWithOptions('pagesatposition', pages);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });

                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_tag_pages_at_position',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var pages = [];
                              jQuery.each(response.data.data, function (idx, val) {
                                  var newplot = {
                                      label: idx,
                                      data: val
                                  };
                                  pages.push(newplot);
                              });
                              plotWithOptions('tagpagesatposition', pages);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });

                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_tag_view_percentage',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var pages = [
                                  {
                                      label: "Tag View Percentage",
                                      data: response.data.data
                                  }
                              ];
                              plotWithOptions('tagviewpercentage', pages);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });

                      jQuery.ajax({
                          url: ajaxurl,
                          method: 'post',
                          data: {
                              'action': 'lti_ltbb_get_tag_click_percentage',
                              'range': daterange
                          },
                          dataType: 'json',
                          success: function (response) {
                              if (response.data.length === 0) {
                                  return;
                              }
                              jQuery(".lti-ltbb-holder").show();
                              var pages = [
                                  {
                                      label: "Tag Click Percentage",
                                      data: response.data.data
                                  }
                              ];
                              plotWithOptions('tagclickpercentage', pages);
                          },
                          error: function (response) {
                              console.log('error');
                              console.log(response);
                          },
                          done: function (response) {
                              console.log('done');
                              console.log(response);
                          }
                      });
                  };

                  load_all();

                  jQuery("#lti-ltbb-range").change(function (e) {
                      e.preventDefault();
                      load_all();
                  });
              });

          </script>

      <div id="content">
        <div id="lt_env">
            <div id="ltdaterange" class="insights-subtitles">
              <h1>Your Content Metrics</h1>
              <div class="metrics-datepicker">
                <span>Select your date range</span>
                <div id="ltdaterangefields">
                  <select name="range" id="lti-ltbb-range" class="lti-ltbb-select">
                    <option value="7days" selected="selected">7 Day Totals</option>
                    <option value="2weeks">2 Week Totals</option>
                    <option value="30days">30 Day Totals</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="insights-inner-padding">
              <div class="lti-ltbb-lists">
                <div id="lti-ltbb-tagsbyviews" class="lti-ltbb-graph">
                  <div class="lti-section-header"><h3>Nothing Here?</h3></div>
                  <div class="lti-errormsg"><p>Data may still be loading from Google Webmaster Tools, or there is an issue with the Google Webmaster Tools connection. Review your configuration settings.</p>
                  </div>
                </div>
                <div id="lti-ltbb-tagsbyclicks" class="lti-ltbb-graph"></div>
              </div>

              <div class="lti-ltbb-holder">
                <div class="lti-section-header insights-title-border">
                  <h3>Overall Views and Clicks</h3>
                </div>
                  <div id="lti-ltbb-aggregatemetricsviews" class="lti-ltbb-graph"></div>
                  <div id="lti-ltbb-aggregatemetricsclicks" class="lti-ltbb-graph"></div>
              </div>
              <div class="lti-ltbb-holder">
                <div class="lti-section-header insights-title-border">
                  <h3>Tag View and Click Percentages</h3>
                </div>
                  <div id="lti-ltbb-tagviewpercentage" class="lti-ltbb-graph"></div>
                  <div id="lti-ltbb-tagclickpercentage" class="lti-ltbb-graph"></div>
              </div>
              <div class="lti-ltbb-holder">
                <div class="lti-section-header insights-title-border">
                  <h3>Overall Position and Click Through Rate</h3>
                </div>
                  <div id="lti-ltbb-aggregatemetricspos" class="lti-ltbb-graph"></div>
                  <div id="lti-ltbb-aggregatemetricsctr" class="lti-ltbb-graph"></div>
              </div>
              <div class="lti-ltbb-holder">
                <div class="lti-section-header insights-title-border">
                  <h3>Number of Pages at Position / Number of Tag Pages at Position</h3>
                </div>
                <div id="lti-ltbb-pagesatposition" class="lti-ltbb-graph"></div>
                <div id="lti-ltbb-tagpagesatposition" class="lti-ltbb-graph"></div>
              </div>
              <div style="clear: both"></div>
            </div>
          </div>
      </div>
    </div>

    <script type="text/javascript">
        jQuery(function () {
            function load_lti_metrics() {
                var metricsrange = jQuery('#lti-ltbb-metricsrange').val();
                jQuery('#lti-ltbb-metrics-parent').html("");
                jQuery.ajax({
                    url: ajaxurl,
                    method: 'post',
                    data: {
                        'action': 'lti_ltbb_get_article_metrics',
                        'range': metricsrange
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.data.data.length === 0) {
                            return;
                        }
                        var template = '<div class="lti-results-box">\n' +
                            '            <div class="row">\n' +
                            '                <div class="column column-10"><span class="lti-results-number">{index}</span></div>\n' +
                            '                <div class="column">\n' +
                            '                    <h4><a href="{guid}" target="_blank">{title}\n' +
                            '                        </a></h4>\n' +
                            '                </div>\n' +
                            '            </div>\n' +
                            '            <div class=" insights-wrap">\n' +
                            '                <div class="lti-words-in-box">Words in title: {words_in_title}</div>\n' +
                            '                <div class="lti-words-in-box">Words in article: {word_count}</div>\n' +
                            '                <div class="lti-words-in-box">Impressions: {impressions}</div>\n' +
                            '                <div class="lti-words-in-box">Clicks: {clicks}</div>\n' +
                            '            </div>\n' +
                            '\n' +
                            '            <h4 class="lti-spacer-top">Tags</h4>\n' +
                            '            <ul class="lti-mini-boxes">\n' +
                            '               {tag_string}\n' +
                            '            </ul>\n' +
                            '\n' +
                            '            <h4>Search Terms</h4>\n' +
                            '            <ul class="lti-mini-boxes">\n' +
                            '                {search_string}\n' +
                            '            </ul>\n' +
                            '        </div>';
                        var articles = "";
                        for (var i = 0; i < response.data.data.articles.length; i++) {
                            var article = response.data.data.articles[i];
                            var thisarticle = template.slice();
                            thisarticle = thisarticle.replace('{index}', i + 1);
                            thisarticle = thisarticle.replace('{guid}', article.guid);
                            thisarticle = thisarticle.replace('{title}', article.title);
                            thisarticle = thisarticle.replace('{words_in_title}', article.words_in_title);
                            thisarticle = thisarticle.replace('{word_count}', article.word_count);
                            thisarticle = thisarticle.replace('{impressions}', article.impressions);
                            thisarticle = thisarticle.replace('{clicks}', article.clicks);
                            var tag_string = "";
                            if(article.tags != null) {
                                for (var t = 0; t < article.tags.length; t++) {
                                    tag_string += '<li>' + article.tags[t] + '</li>';
                                }
                            }
                            thisarticle = thisarticle.replace('{tag_string}', tag_string);
                            var search_string = "";
                            if(article.search != null) {
                                for (var x = 0; x < article.search.length; x++) {
                                    search_string += '<li>' + article.search[x] + '</li>';
                                }
                            }
                            thisarticle = thisarticle.replace('{search_string}', search_string);
                            articles += thisarticle;
                        }
                        jQuery('#lti-ltbb-metrics-parent').html(articles);
                    },
                    error: function (response) {
                        console.log('error');
                        console.log(response);
                    },
                    done: function (response) {
                        console.log('done');
                        console.log(response);
                    }
                });
            }

            load_lti_metrics();

            jQuery("#lti-ltbb-metricsrange").change(function (e) {
                e.preventDefault();
                load_lti_metrics();
            });
        });

    </script>
    <div class="postbox lti-tab insights-blue-border" id="lti-tab-metrics" style="display: none">
      <div class="insights-subtitles">
        <h1>Article Metrics</h1>
        <div class="metrics-datepicker">
          <span>Select your Date Range</span>
          <select name="range" id="lti-ltbb-metricsrange" class="lti-ltbb-select">
            <option value="7days">7 Day Analysis</option>
            <option value="2weeks">2 Week Analysis</option>
            <option value="30days" selected="selected">30 Day Analysis</option>
          </select>
        </div>
      </div>

      <div class="insights-inner-padding">
        <h3>Top Performing Articles</h3>
        <div class="lti-results-box-parent" id="lti-ltbb-metrics-parent"></div>
      </div>
    </div>

    <div class="postbox lti-tab insights-blue-border lti-settings" id="lti-tab-settings" style="display: none;">
      <div class="insights-subtitles">
        <h1>Plugin Settings</h1>
      </div>
      <div class="flex-parent">
        <div class="insights-rightbox">
        <p>Google Webmaster account email currently associated with this site:</p>
          <?php if ($lti_ltbb_email != "") : ?>
        <div class="lti-assoc-email"><?php echo $lti_ltbb_email; ?></div>
          <?php else : ?>
        <div class="lti-assoc-email">There doesn't seem to be an email added yet. Connect below.</div>
          <?php endif; ?>
        <p><hr />
        <p>Need to refresh/change your connection?</p>
       <a class="insights-button" href="<?php echo $lti_ltbb_api_oauth; ?>">Refresh/Change</a>
       <hr />
        <p>Need help?</p>
        <a class="insights-button" href="https://wordpress.org/plugins/laiser-tag/" target="_blank">Wordpress.org site</a></a>
        <a class="insights-button"  href="https://wordpress.org/support/plugin/laiser-tag" target="_blank">Support Site </a>
        <a class="insights-button" href="https://wordpress.org/plugins/laiser-tag/#faq" target="_blank">FAQs</a>
        <hr>
        <p>Need to contact support?</p>
         <a class="insights-button" href="mailto:support@pcis.com">Reach out</a>
      </div>

        <div class="insights-leftbox">
            <?php if($lti_ltbb_status == "Waiting") : ?>
          <div class="lt-first-visit-box">
            <h3>Thank you for installing Laiser Tag Insights!</h3>
            <p>Time to connect Insights to your Google Analytics account. Make sure you have the email from your webmaster, or whomever runs your Analytics account (if that's you, easy! You're halfway there!).</p>
            <p>Click on the Connect button to get started. This will bring you to a Google sign in page, but will redirect you back here after you've completed Google's sign-in process</p>
            <a class="insights-button button-small" href="<?php echo $lti_ltbb_api_oauth; ?>" target="_blank">Connect</a>
          </div>
          <?php else: ?>

          <div class="lt-welcome-box">
            <p>This is where you'll see your connection to the main LT Insights server and authorize access to your Google Webmaster account. </p>
            <p>If there's an issue below, there will be instructions on how to remedy the error. Please contact us if you need further help!</p>
            <div class="lt-thick-blue-line"></div>
          </div>
            <?php endif; ?>

        <div class="flex-parent">

          <div class="lti-site-status">
            <h4>Your current site status</h4>
            <?php if($lti_ltbb_status == "Waiting") : ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-status-new">New</div>
              <p>You have not yet connected your site's Google analytics to Laiser Tag.</p>
              <p>Connect now</p>
            </div>
            <?php elseif ($lti_ltbb_status == "Inactive"): ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-status-inactive">Inactive</div>
              <p>Your connection is either disabled or the permissions process is incomplete. Please refresh your connection.</p>
            </div>
            <?php elseif ($lti_ltbb_status == "Active"): ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-status-active">Active</div>
              <p>Congratulations, nothing more to do here.</p>
            </div>
            <?php elseif (strpos($lti_ltbb_status, 'Processing') !== false): ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-status-processing">Processing</div>
              <p>We're currently capturing your metrics from Google. This shouldn't take long.</p>
            </div>
              <?php endif; ?>
          </div>

          <!-- google connection status here -->
          <div class="lti-google-connection-status">
            <h4>Google connection status</h4>
              <?php if($lti_ltbb_valid_gw) : ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-connection-yes">Active</div>
              <p>Congratulations, nothing more to do here.</p>
            </div>
            <?php else : ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-connection-no">Disconnected</div>
              <p>Please connect your Google analytics account to start receiving metrics.</p>
            </div>
              <?php endif; ?>
          </div>

          <!-- permission status here -->
          <div class="lti-site-permission-status">
            <h4>Is this site allowed for this Google account?</h4>
              <?php if ($lti_ltbb_in_active_site_list) : ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-permission-yes">
                Yes
              </div>
              <p>Congratulations, nothing more to do here.</p>
            </div>
            <?php else: ?>
            <div class="lti-status-box">
              <div class="lti-status-shape lti-permission-no">
                No
              </div>
              <p>Make sure the user you're logging into Google analytics with is the email associated with this site.</p>
            </div>
              <?php endif; ?>
          </div>

        </div>
              <!-- commenting this line out for now - the circles show errors and instructions on how to fix.  -->
            <?php if (!$lti_ltbb_issues) : ?>
                <!-- Error detected in the LTI authorization check; please contact the LTI system admin. -->

            <?php endif; ?>

      </div>
      </div>
    </div>
  </div>
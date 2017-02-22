<?php
/**
 * Created by PhpStorm.
 * User: kbielawiec
 * Date: 12/14/16
 * Time: 11:03 AM
 */

namespace Drupal\casestudies\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use \Drupal\node\Entity\Node;
use Drupal\Core\Entity\ContentEntityStorageBase;



class CaseStudiesForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'casestudies_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        /*
         * Thoughts: for each field element (box for each project) extract html elements
         * from the webpage it links to
         *
         */
        $options = [];
        //echo("Hullo??? <br>");
        $n = 1;
        $m = 1;
        $achieve_url = 'https://www.achieveinternet.com';
        $html = file_get_html("{$achieve_url}/case-studies");
        //Go through every case studies link
        //echo("Is anybody there?? <br>");
        foreach ($html->find('div[class*=views-field-field-photo]') as $project) {
            // echo("Outer loop $n <br>"); //currently looping 21 times
            $n = $n + 1;
            //for every link in the case study (should only be one, try to get rid of the nested loops
            foreach ($project->find('a') as $url) {
                //$url = $project->find('a'); //this produces an array which is an invalid argument
                //echo("Inner loop $m <br>"); //currently looping 21 times
                $m = $m + 1;
                $url_2 = $url->href;
                $html_project = file_get_html("{$achieve_url}{$url_2}");
                $t = $html_project->find('div[id=md1]'); //title
                $t_text = $t[0]->last_child()->innertext;
                //echo("t_text is $t_text <br>");
                $b = $html_project->find('div[id=md2]'); //body
                $b_text = $b[0]->innertext;
                //echo("body is $b_text <br>");
                $s = $html_project->find('div[id=md3]'); //success
                //for ($i = 0; $i < count($s); $i++)
                //{
                //  echo("s array is $s[$i] <br>");
                //}
                if ($s != null) {
                    $s_text = $s[0]->last_child()->innertext;
                } else {
                    //echo("getting in there 1 <br>");
                    $s_1 = $html_project->find('div[class=field--name-field-solution]');
                    if ($s_1 == null) {
                        //echo("null 1 <br>");
                        $s_text = 'no content';
                    } else {
                        //echo("getting in there 2 <br>");
                        $s_2 = $s_1[0]->find('p', -1);
                        if ($s_2 == null) {
                            $s_text = 'no content';
                            //echo("null 2 <br>");
                        } else {
                            //echo("getting in there 3 <br>");
                            $s_text = $s_2->innertext;
                            if ($s_text == null) {
                                $s_text = 'no content';
                                //echo("null 3 <br>");
                            }
                        }
                    }
                }
                //echo("success is $s_text <br>");
                $rows = array(
                    'title' => strip_tags($t_text),
                    'body' => strip_tags($b_text),
                    'success' => strip_tags($s_text)
                );
                $options[] = $rows; //append the newly collected data to options


            }
        }
        $header = array(
            'title' => t('Title'),
            'body' => t('Body'),
            'success' => t('Success')
        );
        $form['table'] = array(
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $options,
            '#empty' => t('No content found')
        );

        $form['submit'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Submit')
            );
        echo '<script>console.log("Returning form in buildForm..")</script>';
        return $form;


    }
    /**
     * {@inheritdoc}
     * Optional.
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $t = "default";
        $complete_form = &$form_state->getCompleteForm();
        $all_rows = &$form_state->getValues();
        $i = 0;
        foreach ($all_rows['table'] as $value) {
            if ($all_rows['table'][$i] != 0) {
                $t = $complete_form['table']['#options'][$i]['title'];
                $b = $complete_form['table']['#options'][$i]['body'];
                $s = $complete_form['table']['#options'][$i]['success'];
                $node = Node::create( array(
                    'type' => 'myform',
                    'title' => $t,
                    'body' => $b,
                    'field_success' => $s,
                    'status' => 1,
                    'promoted' => 1
                    )
                );
                $node->save();
            }
            $i++; // keeps track of the current index
        }
    }

}
?>


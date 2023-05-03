<?php

namespace mrldavies\DocUpload;

use mrldavies\DocUpload\ProcessFile;

class DocUpload
{

    public function run()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
    }

    public function addAdminMenu()
    {
        add_management_page('DocUpload', 'DocUpload', 'manage_options', 'docupload', [$this, 'uploadPage']);
    }

    public function uploadPage()
    {
        if (isset($_POST['submit'])) {
            $this->processUpload();
        }

        $this->renderUploadForm();
    }

    private function renderUploadForm()
    {
?>
        <div class="wrap">
            <h1>DocUpload</h1>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="doc_file" id="doc_file" />
                <?php wp_nonce_field('docupload_process_upload', 'docupload_nonce'); ?>
                <?php submit_button('Upload', 'primary', 'submit', false); ?>
            </form>
        </div>
<?php
    }

    private function processUpload()
    {
        if (!isset($_POST['docupload_nonce']) || !wp_verify_nonce($_POST['docupload_nonce'], 'docupload_process_upload')) {
            echo '<div class="notice notice-error is-dismissible"><p>Security check failed.</p></div>';
            return true;
        }

        if (!isset($_FILES['doc_file']) || !is_uploaded_file($_FILES['doc_file']['tmp_name'])) {
            echo '<div class="notice notice-error is-dismissible"><p>No File has been uploaded.</p></div>';
            return true;
        }

        $file = $_FILES['doc_file']['tmp_name'];
        $postType = 'post';
        $status = 'draft';

        $process = new ProcessFile($file, $postType, $status);

        $process
            ->convertToHtml()
            ->saveFile()
            ->cleanHtml()
            ->post();
    }
}

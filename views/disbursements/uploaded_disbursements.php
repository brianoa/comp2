<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Uploaded Disbursements';
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::button(Html::encode('Approve'), ['id' => 'approve-button', 'class' => 'btn btn-primary']) ?>
<?= Html::button(Html::encode('Reject'), ['id' => 'reject-button', 'class' => 'btn btn-danger mx-4']) ?>

<table class="table">
    <thead>
        <tr>
            <th><?= Html::checkBox('select-all', false, ['id' => 'select-all-checkbox']) ?></th>
            <th>ID</th>
            <th>REFERENCE NAME</th>
            <th>PHONE NUMBER</th>
            <th>AMOUNT</th>
            <th>DISBURSEMENT TYPE</th>
            <th>CREATED BY</th>
            <th>IP ADDRESS</th>
            <th>CREATED AT</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $disbursement): ?>
            <tr data-id="<?= $disbursement->id ?>">
                <td><?= Html::checkBox('disbursements[]', false, ['value' => $disbursement->id, 'class' => 'individual-checkbox']) ?></td>
                <td><?= Html::encode($disbursement->id) ?></td>
                <td><?= Html::encode($disbursement->reference_name) ?></td>
                <td><?= Html::encode($disbursement->phone_number) ?></td>
                <td><?= Html::encode($disbursement->amount) ?></td>
                <td><?= Html::encode($disbursement->disbursement_type) ?></td>
                <td><?= Html::encode($disbursement->created_by) ?></td>
                <td><?= Html::encode($disbursement->ip_address) ?></td>
                <td><?= Html::encode($disbursement->created_at) ?></td>
                <td>
                    <?php
                    $status = $disbursement->status;
                    $status_text = '';

                    switch ($status) {
                        case 4:
                            $status_text = 'Uploaded';
                            break;
                        case 5:
                            $status_text = 'Rejected';
                            break;
                        case 0:
                            $status_text = 'Approved';
                            break;
                        default:
                            $status_text = 'Unknown';
                            break;
                    }

                    echo Html::encode($status_text);
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<?php
$script = <<< JS
    function updateApproveButton() {
        var anyChecked = $('.individual-checkbox:checked').length > 0;
        $('#approve-button,#reject-button').prop('disabled', !anyChecked);
    }

    function updateStatusAndModel(id, status) {
        var statusCell = $('tr[data-id="' + id + '"] .status');
        updateStatusText(statusCell, status);

        $.ajax({
            url: '/disbursements/updatestatus',
            method: 'POST',
            data: { id: id, status: status },
            success: function(response) {
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log(error)
            }
        });
    }

    function updateStatusText(statusElement, status) {
        var statusText = '';
        switch (status) {
            case '4':
                statusText = 'Uploaded';
                break;
            case '5':
                statusText = 'Rejected';
                break;
            case '0':
                statusText = 'Approved';
                break;
            default:
                statusText = 'Unknown';
                break;
        }
        statusElement.text(statusText);
    }

    updateApproveButton();


    $('.individual-checkbox').click(function() {
        updateApproveButton();
    });

    $('.action-dropdown').change(function() {
        var selectedStatus = $(this).val();
        var id = $(this).closest('tr').data('id');
        updateStatusAndModel(id, selectedStatus);
    });


    $('#approve-button').click(function() {
        $('.individual-checkbox:checked').each(function() {
            var id = $(this).closest('tr').data('id');
            updateStatusAndModel(id, '0'); // Set status to Approved (0)
        });
    });
    $('#reject-button').click(function() {
        $('.individual-checkbox:checked').each(function() {
            var id = $(this).closest('tr').data('id');
            updateStatusAndModel(id, '5'); // Set status to Approved (0)
        });
    });

    $('#select-all-checkbox').click(function() {
        $('.individual-checkbox').prop('checked', this.checked);
        updateApproveButton();
    });
JS;

$this->registerJs($script);
?>
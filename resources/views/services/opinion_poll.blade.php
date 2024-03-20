<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css" />
<script type="script/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<style type="text/css">
    #polling_header{
        margin: 0 auto;
        text-align: center;
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }
    ul{
        list-style: none
    }
</style>

<div class="poll_display_block">
    <?php echo '<pre>'; print_r($data->toArray()); echo '</pre>'; ?>
    <div id="polling_header">
        <i class="fa fa-chart-pie"></i> Online Polling
    </div>
    <h5>{{ $data->poll_title }}</h5>
    <ul>
        @foreach($data->PollOptions as $key => $val)
            <li><i class="far fa-circle"></i> {{ $val['option_title'] }}</li>
        @endforeach
    </ul>
</div>
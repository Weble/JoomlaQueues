<?php
defined('_JEXEC') or die();
/**
 * @var  \Weble\JoomlaQueues\Admin\View\Jobs\Html $this
 * @var  \Weble\JoomlaQueues\Admin\Model\Jobs $row
 * @var  \Weble\JoomlaQueues\Admin\Model\Jobs $model
 */
$model = $this->getModel();
?>

@extends('admin:com_queues/Common/browse')

@section('browse-filters')


@stop

@section('browse-table-header')
    {{-- ### HEADER ROW ### --}}
    <tr>
        <th>
            @sortgrid('id')
        </th>
        <th>
            @lang('COM_QUEUES_JOB_FIELD_BUS')
        </th>
        <th>
            @lang('COM_QUEUES_JOB_FIELD_TRANSPORT')
        </th>
        <th width="20%">
            @lang('COM_QUEUES_JOB_FIELD_MESSAGE')
        </th>
        <th width="20%">
            @lang('COM_QUEUES_JOB_FIELD_HANDLERS')
        </th>
        <th width="20%">
            @lang('COM_QUEUES_JOB_FIELD_STATUS')
        </th>
        <th width="20%">
            @sortgrid('sent_on')
        </th>
        <th width="20%">
            @sortgrid('received_on')
        </th>
        <th width="20%">
            @sortgrid('handled_on')
        </th>
        <th width="20%">
            @sortgrid('last_failed_on')
        </th>
    </tr>
@stop

@section('browse-table-body-withrecords')
    {{-- Table body shown when records are present. --}}
    <?php $i = 0; ?>
    @foreach($this->items as $row)
        <tr>

            <td>
                {{ $row->getId() }}
            </td>
            <td>
                {{ $row->bus }}
            </td>
            <td>
                {{ $row->transport }}
            </td>
            <td>
                <strong>{{ get_class($row->envelope()->getMessage()) }}</strong><br/>
                @lang('COM_QUEUES_JOB_FIELD_MESSAGE_ID'): {{ $row->message_id }}
            </td>
            <td>
                @if (count($row->handledBy()) > 0)
                    <ul class="unstyled">
                        @foreach ($row->handledBy() as $handler)
                            <li>
                                <strong>{{ $handler }}</strong>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </td>
            <td>
                @if ($row->waiting())
                    <span class="badge badge-info">
                        @lang('COM_QUEUES_JOBS_WAITING')
                    </span>
                @endif
                @if ($row->hasFailed())
                    <span class="badge" style="background-color: #ee5f5b">
                        @lang('COM_QUEUES_JOBS_FAILED')
                    </span>
                @endif
                @if ($row->received())
                    <span class="badge badge-warning">
                        @lang('COM_QUEUES_JOBS_RECEIVED')
                    </span>
                @endif
                @if ($row->handled())
                    <span class="badge badge-success">
                        @lang('COM_QUEUES_JOBS_HANDLED')
                    </span>
                @endif
            </td>
            <td>
                {{ $row->sent_on }}
            </td>
            <td>
                {{ $row->received_on }}
            </td>
            <td>
                {{ $row->handled_on }}
            </td>
            <td>
                {{ $row->last_failed_on }}
            </td>
        </tr>
    @endforeach
@stop

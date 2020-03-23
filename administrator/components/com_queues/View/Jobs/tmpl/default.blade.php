<?php
defined('_JEXEC') or die();
/**
 * @var  \Weble\JoomlaQueues\Admin\View\Jobs\Html   $this
 * @var  \Weble\JoomlaQueues\Admin\Model\Jobs       $row
 * @var  \Weble\JoomlaQueues\Admin\Model\Jobs       $model
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
        <th width="20%">
            @lang('COM_QUEUES_JOB_FIELD_TRANSPORT')
        </th>
        <th width="20%">
            @lang('COM_QUEUES_JOB_FIELD_MESSAGE_ID')
        </th>
        <th width="20%">
            @sortgrid('available_at')
        </th>
        <th width="60">
            @sortgrid('delivered_at')
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
                {{ $row->message_id }}
            </td>
            <td>
                {{ $row->available_at }}
            </td>
            <td>
                {{ $row->delivered_at }}
            </td>
        </tr>
    @endforeach
@stop

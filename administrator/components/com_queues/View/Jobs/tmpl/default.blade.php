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
        {{-- Row select --}}
        <th width="20">
            <input type="checkbox" name="id" value="" class="hasTooltip" title="id" onclick="checkall" />
        </th>
        <th>
            @sortgrid('id')
        </th>
        <th>
            @lang('COM_QUEUES_MESSAGE_CLASS')
        </th>
        <th width="20%">
            @sortgrid('queue_name')
        </th>
        <th width="20%">
            @sortgrid('created_at')
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
            {{-- Row select --}}
            <td>
                <input type="checkbox" name="cid[]" value="{{ $row->getId() }}" />
            </td>
            <td>
               {{ $row->getId() }}
            </td>
            <td>
                {{ get_class($row->message()->getMessage()) }}
            </td>
            <td>
                {{ $row->queue_name }}
            </td>
            <td>
                {{ $row->created_at }}
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

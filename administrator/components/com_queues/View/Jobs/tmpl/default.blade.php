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
        <th width="20%">
            @sortgrid('published_at')
        </th>
        <th width="20%">
            @sortgrid('queue')
        </th>
        <th width="60">
            @sortgrid('priority')
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
                @jhtml('check', ++$i, $row->getId())
            </td>
            <td>
               {{ $row->getId() }}
            </td>
            <td>
                {{ $row->published_at }}
            </td>
            <td>
                {{ $row->queue }}
            </td>
            <td>
                {{ $row->priority }}
            </td>
        </tr>
    @endforeach
@stop

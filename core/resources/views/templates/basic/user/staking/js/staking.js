"use strict";

$(document).ready(function() {
    let currentPool = null;
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Stake Modal Handler
    $('.stake-btn').on('click', function() {
        var modal = $('#stakeModal');
        var pool = $(this).closest('.staking-card');
        
        // Store pool data
        currentPool = {
            id: $(this).data('pool'),
            name: pool.find('.pool-name').text(),
            type: pool.find('.badge-type').text(),
            apy: parseFloat(pool.find('.badge-apy').text().replace('% APY', '')),
            min: parseFloat($(this).data('min')),
            max: parseFloat($(this).data('max')),
            lockDays: pool.find('.lock-period .info-value').text().split(' ')[0]
        };
        
        console.log('Pool Data:', currentPool); // Debugging
        
        // Update modal UI
        modal.find('input[name=pool_id]').val(currentPool.id);
        modal.find('.pool-name').text(currentPool.name);
        modal.find('.pool-type').text(currentPool.type);
        modal.find('.pool-apy').text(currentPool.apy + '% APY');
        modal.find('.min-amount').text(currentPool.min.toFixed(4));
        modal.find('.max-amount').text(currentPool.max.toFixed(4));
        modal.find('input[name=principal_amount]').attr({
            'min': currentPool.min,
            'max': currentPool.max
        }).val('').trigger('input');

        // Show/hide lock period info
        if (currentPool.type.toLowerCase() === 'locked') {
            modal.find('.lock-period-info').show().find('.lock-days').text(currentPool.lockDays);
        } else {
            modal.find('.lock-period-info').hide();
        }

        // Reset calculator
        resetCalculator();
    });

    // Max amount button
    $('.stake-max').on('click', function() {
        if (!currentPool) return;
        
        var fundingBalance = parseFloat('{{ $fundingWallet->balance ?? 0 }}');
        var spotBalance = parseFloat('{{ $spotWallet->balance ?? 0 }}');
        var totalBalance = fundingBalance + spotBalance;
        var maxAmount = Math.min(totalBalance, currentPool.max);
        
        $('input[name=principal_amount]').val(maxAmount.toFixed(4)).trigger('input');
    });

    // Calculate earnings when amount changes
    $('input[name=principal_amount]').on('input', calculateEarnings);

    function calculateEarnings() {
        if (!currentPool) {
            console.error('No current pool selected');
            return;
        }

        var amount = parseFloat($('input[name=principal_amount]').val()) || 0;
        console.log('Calculating for amount:', amount); // Debugging
        
        // Validate amount is within min/max range
        if (amount < currentPool.min) {
            amount = 0;
            $('input[name=principal_amount]').val('');
        } else if (amount > currentPool.max) {
            amount = currentPool.max;
            $('input[name=principal_amount]').val(currentPool.max.toFixed(4));
        }

        if (isNaN(amount)) {
            resetCalculator();
            return;
        }

        // Calculate earnings (simple interest)
        const apyDecimal = currentPool.apy / 100;
        const dailyEarnings = (amount * apyDecimal) / 365;
        const monthlyEarnings = dailyEarnings * 30;
        const yearlyEarnings = amount * apyDecimal;
        const totalValue = amount + yearlyEarnings;

        console.log('Calculated values:', {dailyEarnings, monthlyEarnings, yearlyEarnings, totalValue}); // Debugging

        // Update UI
        $('.daily-earnings').text(dailyEarnings.toFixed(6) + ' USDT');
        $('.monthly-earnings').text(monthlyEarnings.toFixed(6) + ' USDT');
        $('.yearly-earnings').text(yearlyEarnings.toFixed(6) + ' USDT');
        $('.total-value').text(totalValue.toFixed(6) + ' USDT');
    }

    function resetCalculator() {
        $('.daily-earnings, .monthly-earnings, .yearly-earnings, .total-value').text('0.00 USDT');
    }

    // Unstake handler
    $('.unstake-btn').on('click', function() {
        var stakeId = $(this).data('stake');
        $('#unstakeModal input[name=stake_id]').val(stakeId);
    });

    // Compound handler
    $('.compound-btn').on('click', function() {
        var stakeId = $(this).data('stake');
        $('#compoundModal input[name=stake_id]').val(stakeId);
    });
});
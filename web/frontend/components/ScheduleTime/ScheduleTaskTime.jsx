import {
    BlockStack,
    Box,
    TextField,
    Popover,
    Card,
    Icon,
    DatePicker,
    Checkbox,
    Select,
    Divider,
  } from "@shopify/polaris";
  import { useState, useCallback } from "react";
  import { CalendarIcon } from '@shopify/polaris-icons';
  import CustomTimePicker from "./CustomTimePicker";
  
  export default function ScheduleTaskTime() {
    const [scheduleVisible, setScheduleVisible] = useState(false);
    const [revertVisible, setRevertVisible] = useState(false);
    const [scheduleDate, setScheduleDate] = useState(new Date());
    const [revertDate, setRevertDate] = useState(new Date());
    const [scheduleTime, setScheduleTime] = useState('12:00');
    const [revertTime, setRevertTime] = useState('12:00');
    
    const [{ month: scheduleMonth, year: scheduleYear }, setScheduleMonthYear] = useState({
      month: scheduleDate.getMonth(),
      year: scheduleDate.getFullYear(),
    });
  
    const [{ month: revertMonth, year: revertYear }, setRevertMonthYear] = useState({
      month: revertDate.getMonth(),
      year: revertDate.getFullYear(),
    });
  
    const formattedScheduleValue = scheduleDate.toISOString().slice(0, 10);
    const formattedRevertValue = revertDate.toISOString().slice(0, 10);
  
    const handleScheduleMonthChange = useCallback((month, year) => {
      setScheduleMonthYear({ month, year });
    }, []);
  
    const handleRevertMonthChange = useCallback((month, year) => {
      setRevertMonthYear({ month, year });
    }, []);
  
    const handleScheduleDateSelection = useCallback(({ start: newScheduleDate }) => {
      setScheduleDate(newScheduleDate);
      setScheduleVisible(false);
    }, []);
  
    const handleRevertDateSelection = useCallback(({ start: newRevertDate }) => {
      setRevertDate(newRevertDate);
      setRevertVisible(false);
    }, []);
  
    const handleScheduleTimeChange = useCallback((value) => {
      setScheduleTime(value);
    }, []);
  
    const handleRevertTimeChange = useCallback((value) => {
      setRevertTime(value);
    }, []);
  
    const [isOneTime, setIsOneTime] = useState(false);
    const handleIsOneTimeSchedule = useCallback((newChecked) => setIsOneTime(newChecked), []);
  
    const rescheduleFrequency = [
      { label: 'Everyday (+1 day)', value: 'everyday' },
      { label: 'Every 2nd day (+2 day)', value: 'every_two_day' },
      { label: 'Every week (+1 week)', value: 'every_week' },
      { label: 'Every Month (+1 month)', value: 'every_month' },
    ];
  
    const [rescheduleSelect, handleReScheduleSelect] = useState('');
    const handleReScheduleSelectChange = useCallback((value) => handleReScheduleSelect(value), []);
  
    return (
      <Box paddingBlockStart={20}>
        <Card title="Task Scheduling" sectioned>
          <BlockStack spacing="loose">
            {/* Schedule Section */}
            <Card sectioned title="Schedule Task">
              <BlockStack spacing="tight">
                <Popover
                  active={scheduleVisible}
                  autofocusTarget="none"
                  fullWidth
                  onClose={() => setScheduleVisible(false)}
                  activator={
                    <TextField
                      label="Start date"
                      prefix={<Icon source={CalendarIcon} />}
                      value={formattedScheduleValue}
                      onFocus={() => setScheduleVisible(true)}
                      onChange={() => {}}
                      autoComplete="off"
                    />
                  }
                >
                  <Card>
                    <DatePicker
                      month={scheduleMonth}
                      year={scheduleYear}
                      selected={scheduleDate}
                      onMonthChange={handleScheduleMonthChange}
                      onChange={handleScheduleDateSelection}
                    />
                  </Card>
                </Popover>
                <CustomTimePicker
                  label="Time"
                  onChange={handleScheduleTimeChange}
                  value={scheduleTime}
                />
              </BlockStack>
            </Card>
  
            {/* Revert Section */}
            <Divider />
            <Card sectioned title="Revert Task">
              <BlockStack spacing="tight">
                <Popover
                  active={revertVisible}
                  autofocusTarget="none"
                  fullWidth
                  onClose={() => setRevertVisible(false)}
                  activator={
                    <TextField
                      label="Revert date"
                      prefix={<Icon source={CalendarIcon} />}
                      value={formattedRevertValue}
                      onFocus={() => setRevertVisible(true)}
                      onChange={() => {}}
                      autoComplete="off"
                    />
                  }
                >
                  <Card>
                    <DatePicker
                      month={revertMonth}
                      year={revertYear}
                      selected={revertDate}
                      onMonthChange={handleRevertMonthChange}
                      onChange={handleRevertDateSelection}
                    />
                  </Card>
                </Popover>
                <CustomTimePicker
                  label="Revert Time"
                  onChange={handleRevertTimeChange}
                  value={revertTime}
                />
              </BlockStack>
            </Card>
  
            {/* Reschedule Option */}
            <Checkbox
              label="Only schedule one time"
              checked={isOneTime}
              onChange={handleIsOneTimeSchedule}
            />
            {!isOneTime && (
              <Select
                label="Reschedule Frequency"
                options={rescheduleFrequency}
                onChange={handleReScheduleSelectChange}
                value={rescheduleSelect}
              />
            )}
          </BlockStack>
        </Card>
      </Box>
    );
  }
  
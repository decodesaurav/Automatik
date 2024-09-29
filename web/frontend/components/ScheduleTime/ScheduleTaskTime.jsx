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
  Text,
  InlineStack,
  InlineError,
} from "@shopify/polaris";
import { useState, useCallback, useReducer } from "react";
import { CalendarIcon } from '@shopify/polaris-icons';
import CustomTimePicker from "./CustomTimePicker";

export default function ScheduleTaskTime({stateData,dispatch,actionTypes,errorData}) {
  let state = stateData;
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
      dispatch({
          type: actionTypes.HANDLE_SCHEDULE_TIME_CHANGE,
          payload: { scheduled_at_date: newScheduleDate.toISOString().slice(0, 10) }
      });
      setScheduleVisible(false);
  }, [dispatch]);

  const handleRevertDateSelection = useCallback(({ start: newRevertDate }) => {
      setRevertDate(newRevertDate);
      dispatch({
          type: actionTypes.HANDLE_REVERT_TIME_CHANGE,
          payload: { revert_at_date: newRevertDate.toISOString().slice(0, 10) }
      });
      setRevertVisible(false);
  }, [dispatch]);

  const handleScheduleTimeChange = useCallback((value) => {
      setScheduleTime(value);
      dispatch({
          type: actionTypes.HANDLE_SCHEDULE_TIME_CHANGE,
          payload: { schedule_at_time: value }
      });
  }, [dispatch]);

  const handleRevertTimeChange = useCallback((value) => {
      setRevertTime(value);
      dispatch({
          type: actionTypes.HANDLE_REVERT_TIME_CHANGE,
          payload: { revert_at_time: value }
      });
  }, [dispatch]);

  const handleIsOneTimeSchedule = useCallback((newChecked) => {
      dispatch({
          type: actionTypes.HANDLE_SCHEDULE_ONE_TIME_ONLY,
      });
  }, [dispatch]);

  const handleRevertSchedule = useCallback((newChecked) => {
      dispatch({
          type: actionTypes.HANDLE_REVERT_SCHEDULE,
      });
  }, [dispatch]);

  const handleTaskNameChange = useCallback((value) => {
    dispatch({
        type: actionTypes.HANDLE_TASK_NAME_CHANGE,
        payload: value
    });
}, [dispatch]);

  const rescheduleFrequencyOptions = [
      { label: 'Everyday (+1 day)', value: 'everyday' },
      { label: 'Every 2nd day (+2 days)', value: 'every_two_day' },
      { label: 'Every week (+1 week)', value: 'every_week' },
      { label: 'Every Month (+1 month)', value: 'every_month' },
  ];

  const [rescheduleSelect, setRescheduleSelect] = useState('');
  const handleReScheduleSelectChange = useCallback((value) => {
      setRescheduleSelect(value);
      dispatch({
          type: actionTypes.HANDLE_SCHEDULE_TIME_CHANGE,
          payload: { reschedule_frequency: value }
      });
  }, [dispatch]);

  return (
      <Box paddingBlockStart={400}>
          {/* Schedule Section */}
          <Box paddingBlockEnd={400}>
            <TextField
                    label="Task Name"
                    placeholder="Enter task name (please use name which reminds you what task does)"
                    value={state.taskName}
                    onChange={handleTaskNameChange}
                    autoComplete="off"
                /> 
                {errorData.task_name && (
                    <InlineError message={errorData.task_name} fieldID="taskName" />
                )}
            </Box>
          <InlineStack>
              <Box minWidth="350px" paddingInlineEnd={400}>
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
                  {errorData.scheduled_at_date && (
                      <InlineError message={errorData.scheduled_at_date} fieldID="scheduleDate" />
                  )}
              </Box>
              <BlockStack>
                <CustomTimePicker
                    label="Time"
                    onChange={handleScheduleTimeChange}
                    value={scheduleTime}
                />      
                {errorData.schedule_at_time && (
                  <InlineError message={errorData.schedule_at_time} fieldID="scheduleTime" />
                )}
              </BlockStack>
          </InlineStack>

          <Box paddingBlockStart={400}>
              <Divider borderWidth="050" />
          </Box>

          {/* Reschedule */}
          <Box paddingBlockStart={400}>
              <BlockStack>
                  <Text variant="headingSm">Reschedule</Text>
                  <InlineStack gap={400}>
                      <Checkbox
                          label="Only schedule one time"
                          checked={state.scheduleOnlyOneTime}
                          onChange={handleIsOneTimeSchedule}
                      />
                      {!state.scheduleOnlyOneTime && (
                          <Select
                              label="Reschedule Frequency"
                              options={rescheduleFrequencyOptions}
                              onChange={handleReScheduleSelectChange}
                              value={rescheduleSelect}
                          />
                      )}
                  </InlineStack>
                  {errorData.reschedule_frequency && (
                      <InlineError message={errorData.reschedule_frequency} fieldID="rescheduleFrequency" />
                  )}
              </BlockStack>
          </Box>

          <Box paddingBlockStart={400}>
              <Divider borderWidth="050" />
          </Box>

          {/* Revert */}
          <Box paddingBlockStart={400}>
              <BlockStack>
                  <Text variant="headingSm">Revert Back (if needed)</Text>
                  <InlineStack gap={800}>
                      <Checkbox
                          label="I want to revert back changes some time"
                          checked={state.revertSchedule}
                          onChange={handleRevertSchedule}
                      />
                      {state.revertSchedule && (
                          <InlineStack>
                              <Box minWidth="350px" paddingInlineEnd={400}>
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
                                  {errorData.revert_at_date && (
                                      <InlineError message={errorData.revert_at_date} fieldID="revertDate" />
                                  )}
                              </Box>
                              <BlockStack>
                                <CustomTimePicker
                                    label="Revert Time"
                                    onChange={handleRevertTimeChange}
                                    value={revertTime}
                                />
                                {errorData.revert_at_time && (
                                    <InlineError message={errorData.revert_at_time} fieldID="revertTime" />
                                )}
                              </BlockStack>
                          </InlineStack>
                      )}
                  </InlineStack>
              </BlockStack>
          </Box>
      </Box>
  );
}
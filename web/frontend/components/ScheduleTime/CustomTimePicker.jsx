import React, { useState, useCallback } from 'react';
import { Select, InlineStack } from '@shopify/polaris';

const generateTimeOptions = (limit) => {
  const options = [];
  for (let i = 0; i < limit; i++) {
    const value = i < 10 ? `0${i}` : `${i}`;
    options.push({ label: value, value: value });
  }
  return options;
};

export default function CustomTimePicker({ label, value, onChange }) {
  const [hours, setHours] = useState(value.split(':')[0]);
  const [minutes, setMinutes] = useState(value.split(':')[1]);

  const hourOptions = generateTimeOptions(24);
  const minuteOptions = generateTimeOptions(60);

  const handleHoursChange = useCallback((newHour) => {
    setHours(newHour);
    onChange(`${newHour}:${minutes}`);
  }, [minutes, onChange]);

  const handleMinutesChange = useCallback((newMinute) => {
    setMinutes(newMinute);
    onChange(`${hours}:${newMinute}`);
  }, [hours, onChange]);

  return (
    <div>
      <label style={{ marginBottom: '10px', display: 'block' }}>{label}</label>
      <InlineStack>
        <Select
          label="Hours"
          options={hourOptions}
          onChange={handleHoursChange}
          value={hours}
        />
        <Select
          label="Minutes"
          options={minuteOptions}
          onChange={handleMinutesChange}
          value={minutes}
        />
      </InlineStack>
    </div>
  );
}
